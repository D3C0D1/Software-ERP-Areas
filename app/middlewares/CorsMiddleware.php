<?php
namespace App\Middlewares;

class CorsMiddleware
{
    /**
     * Aplica los headers de CORS para permitir solicitudes desde el Frontend (React, Vue, etc.)
     * o clientes externos autorizados.
     */
    public function handle()
    {
        // En local leemos el Origin dinámicamente para evitar el bloqueo CORS estricto de Chrome
        $allowedOrigin = $_SERVER['HTTP_ORIGIN'] ?? '*';

        if ($allowedOrigin === '*') {
            // Si el Origin no viene en la cabecera (Petición directa), usamos el host actual 
            $allowedOrigin = 'http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
        }

        header("Access-Control-Allow-Origin: {$allowedOrigin}");
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
        header("Access-Control-Allow-Credentials: true");

        // Si es una solicitud de tipo OPTIONS (Pre-flight), terminamos la ejecución aquí
        // indicando que es válida, para que el navegador proceda con la petición real.
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }
    }
}