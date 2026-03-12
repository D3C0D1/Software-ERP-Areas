<?php
namespace App\Middlewares;

class CsrfMiddleware
{
    /**
     * Genera un token CSRF si no existe
     */
    public static function generateToken()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Valida el token CSRF que viene por POST o Headers
     */
    public function handle()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Si la petición es GET, OPTIONS, HEAD, no require CSRF
        if (in_array($_SERVER['REQUEST_METHOD'], ['GET', 'OPTIONS', 'HEAD'])) {
            return;
        }

        $headers = function_exists('apache_request_headers') ? apache_request_headers() : [];
        $token = $_POST['csrf_token'] ?? $headers['X-CSRF-TOKEN'] ?? '';

        // Si el payload es JSON
        if (strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false) {
            $input = json_decode(file_get_contents('php://input'), true);
            $token = $input['csrf_token'] ?? $token;
        }

        if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
            http_response_code(403);
            echo json_encode([
                "status" => "error",
                "message" => "Token de seguridad CSRF inválido o expirado."
            ]);
            exit;
        }
    }
}