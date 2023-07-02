<?php 
// Permitir el acceso desde cualquier origen
header("Access-Control-Allow-Origin: *");

// Permitir métodos específicos (GET, POST, etc.)
header("Access-Control-Allow-Methods: GET, POST,PUT,DELETE");

// Permitir encabezados personalizados
header("Access-Control-Allow-Headers: Content-Type");

// Permitir credenciales (cookies, autenticación, etc.)
header("Access-Control-Allow-Credentials: true");

// Establecer la duración de la caché de las respuestas preflight (opcional)
header("Access-Control-Max-Age: 3600");
?>