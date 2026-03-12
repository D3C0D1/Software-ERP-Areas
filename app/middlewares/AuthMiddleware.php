<?php
namespace App\Middlewares;

class AuthMiddleware
{

    // Ejecutar validaciones previas al controlador
    public function handle()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // 1. Validar existencia de sesión validada
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(["status" => "error", "message" => "No autorizado. Inicie sesión."]);
            exit;
        }

        // Actualizar last_activity en BD (sin timeout de expiración)
        $this->updateDBLastActivity($_SESSION['user_id']);
    }

    // Autorización por Rol (para APIs - devuelve JSON)
    public function authorizeRoles($allowedRoles)
    {
        $this->handle(); // Asegurar estar autenticado

        $userRole = $_SESSION['role'] ?? null;
        if (!in_array($userRole, $allowedRoles)) {
            http_response_code(403);
            echo json_encode(["status" => "error", "message" => "Acceso denegado. Permisos insuficientes."]);
            exit;
        }
    }

    // Autorización por Rol (para Vistas HTML - redirige al dashboard)
    public function authorizeView($allowedRoles, $basePath = '')
    {
        // Verificar sesión activa
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Sin sesión → redirigir al login
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . $basePath . '/login');
            exit;
        }

        // Sin rol permitido → redirigir al dashboard con aviso en URL
        $userRole = $_SESSION['role'] ?? null;
        if (!in_array($userRole, $allowedRoles)) {
            header('Location: ' . $basePath . '/dashboard?error=permisos');
            exit;
        }
    }

    private function updateDBLastActivity($userId)
    {
        try {
            // Nota: Aquí usaríamos el modelo User o inyectaríamos el Repository.
            $db = \Config\Database::getInstance();
            $stmt = $db->prepare("UPDATE usuarios SET last_activity = NOW() WHERE id = :id");
            $stmt->execute(['id' => $userId]);
        }
        catch (\PDOException $e) {
        // Log error
        }
    }
}