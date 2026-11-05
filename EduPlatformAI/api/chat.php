<?php

declare(strict_types=1);

header("Content-Type: application/json; charset=utf-8");

function responder(int $status, array $data): never
{
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Allow: POST");
    responder(405, ["error" => "Este endpoint solo acepta solicitudes POST."]);
}

if (!extension_loaded("curl")) {
    error_log("EduPlatform AI: la extensión cURL de PHP no está habilitada.");
    responder(500, ["error" => "El servicio de chat no está disponible en este momento."]);
}

$config = require __DIR__ . "/../config/gemini.php";
$apiKey = trim($config["api_key"]);
$model = trim($config["model"]);

if ($apiKey === "" || $model === "") {
    error_log("EduPlatform AI: configura GEMINI_API_KEY y GEMINI_MODEL en el entorno de PHP.");
    responder(503, ["error" => "El servicio de IA no está configurado."]);
}

$contentLength = (int) ($_SERVER["CONTENT_LENGTH"] ?? 0);
if ($contentLength > 524288) {
    responder(413, ["error" => "La conversación es demasiado larga. Inicia un chat nuevo e inténtalo otra vez."]);
}

$rawBody = file_get_contents("php://input");
if (strlen($rawBody) > 524288) {
    responder(413, ["error" => "La conversación es demasiado larga. Inicia un chat nuevo e inténtalo otra vez."]);
}

$payload = json_decode($rawBody, true);

if (!is_array($payload) || !isset($payload["messages"]) || !is_array($payload["messages"])) {
    responder(400, ["error" => "La solicitud no contiene una conversación válida."]);
}

if (count($payload["messages"]) < 1 || count($payload["messages"]) > 30) {
    responder(400, ["error" => "La conversación debe contener entre 1 y 30 mensajes."]);
}

$contents = [];
foreach ($payload["messages"] as $message) {
    if (
        !is_array($message)
        || !isset($message["role"], $message["content"])
        || !in_array($message["role"], ["user", "model"], true)
        || !is_string($message["content"])
    ) {
        responder(400, ["error" => "La conversación contiene un mensaje no válido."]);
    }

    $text = trim($message["content"]);
    if ($text === "" || mb_strlen($text, "UTF-8") > 4000) {
        responder(400, ["error" => "Cada mensaje debe tener entre 1 y 4000 caracteres."]);
    }

    $contents[] = [
        "role" => $message["role"],
        "parts" => [["text" => $text]]
    ];
}

$requestBody = json_encode([
    "systemInstruction" => [
        "parts" => [[
            "text" => "Eres EduBot, un asistente educativo amable. Explica con claridad, adapta las respuestas al nivel del estudiante y fomenta el aprendizaje."
        ]]
    ],
    "contents" => $contents,
    "generationConfig" => [
        "maxOutputTokens" => 2048,
        "temperature" => 0.7
    ]
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

$url = "https://generativelanguage.googleapis.com/v1beta/models/"
    . rawurlencode($model)
    . ":generateContent";

$curl = curl_init($url);
curl_setopt_array($curl, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $requestBody,
    CURLOPT_HTTPHEADER => [
        "Content-Type: application/json",
        "x-goog-api-key: " . $apiKey
    ],
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CONNECTTIMEOUT => 10,
    CURLOPT_TIMEOUT => 45
]);

$responseBody = curl_exec($curl);
$curlError = curl_error($curl);
$statusCode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
curl_close($curl);

if ($responseBody === false) {
    error_log("EduPlatform AI: falló la conexión con Gemini: " . $curlError);
    responder(502, ["error" => "No se pudo conectar con el servicio de IA. Inténtalo de nuevo."]);
}

$response = json_decode($responseBody, true);
if ($statusCode < 200 || $statusCode >= 300) {
    $providerMessage = $response["error"]["message"] ?? "Respuesta HTTP " . $statusCode;
    error_log("EduPlatform AI: Gemini respondió con error: " . $providerMessage);

    if ($statusCode === 429) {
        responder(429, ["error" => "El servicio de IA está ocupado. Espera un momento e inténtalo de nuevo."]);
    }

    responder(502, ["error" => "El servicio de IA no pudo procesar la pregunta. Inténtalo de nuevo."]);
}

$parts = $response["candidates"][0]["content"]["parts"] ?? [];
$reply = "";
foreach ($parts as $part) {
    if (isset($part["text"]) && is_string($part["text"])) {
        $reply .= $part["text"];
    }
}

if (trim($reply) === "") {
    error_log("EduPlatform AI: Gemini devolvió una respuesta vacía.");
    responder(502, ["error" => "La IA no generó una respuesta. Inténtalo de nuevo."]);
}

responder(200, ["reply" => trim($reply)]);