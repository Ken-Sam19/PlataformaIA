
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduPlatform AI</title>

    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div class="app">

    <aside class="sidebar">

        <div class="logo">
            <span class="logo-icon">✦</span>
            <h2>EduPlatform <span>AI</span></h2>
        </div>

        <button class="new-chat" type="button" onclick="nuevoChat()">
            + Nueva conversación
        </button>

        <p class="menu-title">MENÚ PRINCIPAL</p>

        <nav>
            <a class="active" href="#">✧ Asistente IA</a>
            <a href="#">▤ Mis cursos</a>
            <a href="#">◷ Historial</a>
            <a href="#">⚙ Configuración</a>
        </nav>

        <div class="profile">
            <div class="avatar">K</div>
            <div>
                <strong>Estudiante</strong>
                <small>Mi cuenta</small>
            </div>
        </div>

    </aside>

    <main class="main">

        <header class="topbar">
            <div>
                <h3>Asistente virtual</h3>
                <small>Tu compañero de aprendizaje</small>
            </div>

            <div class="status">
                <span></span> Sistema disponible
            </div>
        </header>

        <section class="chat-area" id="chatArea" role="log" aria-live="polite" aria-label="Conversación con EduBot">

            <div class="welcome" id="welcome">

                <div class="ai-icon">✦</div>

                <h1>¿Qué vamos a aprender hoy?</h1>

                <p>
                    Soy EduBot, tu asistente inteligente.
                    Pregúntame lo que quieras y aprendamos juntos.
                </p>

                <div class="suggestions">

                    <button onclick="preguntar('Explícame qué es JavaScript')">
                        💻 Aprender programación
                    </button>

                    <button onclick="preguntar('Explícame qué es una base de datos')">
                        🗄 Bases de datos
                    </button>

                    <button onclick="preguntar('Ayúdame a estudiar inglés')">
                        🌎 Aprender inglés
                    </button>

                    <button onclick="preguntar('Explícame qué es inteligencia artificial')">
                        🤖 Inteligencia artificial
                    </button>

                </div>

            </div>

        </section>

        <footer class="chat-footer">

            <div class="input-container">

                <input
                    type="text"
                    id="messageInput"
                    placeholder="Escribe tu pregunta aquí..."
                    autocomplete="off"
                    maxlength="4000"
                    aria-label="Escribe tu pregunta"
                >

                <button id="sendButton" type="button" aria-label="Enviar mensaje" onclick="enviarMensaje()">
                    ➤
                </button>

            </div>

            <small>
                EduPlatform AI puede cometer errores.
                Verifica la información importante.
            </small>

        </footer>

    </main>

</div>

<script src="assets/js/app.js"></script>

</body>
</html>
