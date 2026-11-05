# EduPlatform AI

Chat educativo en PHP que conecta la interfaz con la API de Gemini.

## Configuración

1. Habilita la extensión PHP `curl`.
2. Configura `GEMINI_API_KEY` y `GEMINI_MODEL` en el entorno de Apache/PHP. En WAMP puedes agregar estas líneas a la configuración de Apache (por ejemplo, al VirtualHost del proyecto), reemplazar el modelo por uno habilitado en tu cuenta y reiniciar Apache:

   ```apache
   SetEnv GEMINI_API_KEY "tu-clave-de-Gemini"
   SetEnv GEMINI_MODEL "modelo-disponible-en-Gemini"
   ```

   No guardes la clave en archivos públicos ni en el repositorio. Si la clave que estaba en `config/gemini.php` era real, revócala y genera una nueva antes de continuar.
3. Abre el proyecto desde el servidor local de WAMP; el navegador no debe abrir `index.php` directamente como archivo.

El endpoint `api/chat.php` recibe un arreglo `messages` con los roles `user` y `model`. La clave solo se usa en PHP y no se envía al navegador.