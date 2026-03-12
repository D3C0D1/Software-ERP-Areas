<?php
/**
 * FRONT CONTROLLER
 * Único punto de entrada a la aplicación.
 */

// 1. Mostrar errores en desarrollo (Apagar en la versión final de producción)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Configurar zona horaria para Colombia
date_default_timezone_set('America/Bogota');


// 2. Autocargador Simple (Autoloader MVC)
// Este script carga las clases automáticamente basándose en los Namespaces y Directorios.
spl_autoload_register(function ($class) {
    // Convierte el namespace (Ej: App\Controllers\AuthController) a ruta (App/Controllers/AuthController.php)
    $classPath = str_replace('\\', DIRECTORY_SEPARATOR, $class);

    // Mapeo del prefijo Base
    $baseDir = dirname(__DIR__) . DIRECTORY_SEPARATOR;

    // Corrección para la capitalización de carpetas base (App => app, Config => config, etc.)
    $partials = explode(DIRECTORY_SEPARATOR, $classPath);
    if (isset($partials[0])) {
        $partials[0] = strtolower($partials[0]);
    }

    // Si la carpeta base es 'app' y contiene otra carpeta (ej: Controllers => controllers, Middlewares => middlewares)
    if ($partials[0] === 'app' && isset($partials[1])) {
        $partials[1] = strtolower($partials[1]);
    }

    $file = $baseDir . implode(DIRECTORY_SEPARATOR, $partials) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});

// 3. Ejecutar el Middleware Global CORS
$cors = new \App\Middlewares\CorsMiddleware();
$cors->handle();

// 4. Capturar la URI y el Método HTTP
// Obtenemos la ruta ignorando el query string (?id=1)
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Eliminamos dinámicamente el prefijo de la carpeta del proyecto si existe en entornos locales (ej. /Bnner)
$scriptName = dirname($_SERVER['SCRIPT_NAME']); // /Bnner/public o /Bnner
$basePath = str_replace('/public', '', $scriptName);
if ($basePath === '/' || $basePath === '\\') {
    $basePath = '';
}

if ($basePath !== '' && strpos($requestUri, $basePath) === 0) {
    $uri = substr($requestUri, strlen($basePath));
}
else {
    $uri = $requestUri;
}

// Evitar doble slash por configuraciones extrañas de apache local
$uri = '/' . ltrim($uri, '/');

// Si la URI arranca con /public, lo removemos iterativamente para que las rutas coincidan desde la raíz (/)
while (strpos($uri, '/public') === 0) {
    $uri = substr($uri, 7);
    $uri = '/' . ltrim($uri, '/');
}

// Mapeo crudo (Fallback sin .htaccess): Si dice /index.php, también se lo quitamos para que el enrutador lea la ruta limpia
if (strpos($uri, '/index.php') === 0) {
    $uri = substr($uri, 10);
    $uri = '/' . ltrim($uri, '/');
}
$method = $_SERVER['REQUEST_METHOD'];

// Header JSON exclusivo para las API
if (strpos($uri, '/api') === 0) {
    header('Content-Type: application/json; charset=utf-8');
}

// 5. Instanciar el Enrutador y Despachar
$router = new \Routes\ApiRoutes();
$router->dispatch($method, $uri);