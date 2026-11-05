
const chatArea = document.getElementById("chatArea");
const messageInput = document.getElementById("messageInput");
const sendButton = document.getElementById("sendButton");

let conversation = [];
let currentRequest = null;

function agregarMensaje(texto, tipo) {
    const mensaje = document.createElement("div");

    mensaje.classList.add("message", tipo);
    mensaje.textContent = texto;

    chatArea.appendChild(mensaje);
    chatArea.scrollTop = chatArea.scrollHeight;
    return mensaje;
}

function nuevoChat() {
    if (currentRequest) {
        currentRequest.abort();
        currentRequest = null;
    }

    conversation = [];
    chatArea.innerHTML = `
        <div class="welcome" id="welcome">
            <div class="ai-icon">✦</div>
            <h1>¿Qué vamos a aprender hoy?</h1>
            <p>Soy EduBot, tu asistente inteligente.
            Pregúntame lo que quieras.</p>
            <div class="suggestions">
                <button onclick="preguntar('Explícame qué es JavaScript')">💻 Aprender programación</button>
                <button onclick="preguntar('Explícame qué es una base de datos')">🗄 Bases de datos</button>
                <button onclick="preguntar('Ayúdame a estudiar inglés')">🌎 Aprender inglés</button>
                <button onclick="preguntar('Explícame qué es inteligencia artificial')">🤖 Inteligencia artificial</button>
            </div>
        </div>
    `;
    messageInput.value = "";
    messageInput.disabled = false;
    sendButton.disabled = false;
    messageInput.focus();
}

function preguntar(texto) {
    if (currentRequest) return;
    messageInput.value = texto;
    enviarMensaje();
}

async function enviarMensaje() {
    const texto = messageInput.value.trim();

    if (texto === "" || currentRequest) return;

    const bienvenida = document.getElementById("welcome");
    if (bienvenida) bienvenida.remove();
    agregarMensaje(texto, "user-message");
    messageInput.value = "";

    conversation.push({ role: "user", content: texto });
    const requestController = new AbortController();
    currentRequest = requestController;
    messageInput.disabled = true;
    sendButton.disabled = true;

    const loadingMessage = agregarMensaje("EduBot está pensando…", "bot-message loading-message");

    try {
        const response = await fetch("api/chat.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ messages: conversation }),
            signal: requestController.signal
        });
        const data = await response.json();

        if (currentRequest !== requestController) return;

        if (!response.ok) {
            throw new Error(data.error || "No se pudo obtener una respuesta. Inténtalo de nuevo.");
        }

        if (!data.reply || typeof data.reply !== "string") {
            throw new Error("El servicio devolvió una respuesta no válida. Inténtalo de nuevo.");
        }

        conversation.push({ role: "model", content: data.reply });
        while (conversation.length > 30) conversation.shift();
        if (conversation[0]?.role === "model") conversation.shift();
        loadingMessage.textContent = data.reply;
        loadingMessage.classList.remove("loading-message");
    } catch (error) {
        loadingMessage.remove();
        if (error.name !== "AbortError") {
            agregarMensaje(error.message || "Ocurrió un error al enviar tu pregunta.", "bot-message error-message");
            conversation.pop();
        }
    } finally {
        if (currentRequest === requestController) {
            currentRequest = null;
            messageInput.disabled = false;
            sendButton.disabled = false;
            messageInput.focus();
        }
    }
}

messageInput.addEventListener("keydown", function(event) {
    if (event.key === "Enter" && !event.isComposing) {
        event.preventDefault();
        enviarMensaje();
    }
});
