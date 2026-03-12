<?php
namespace App\Controllers;

use PDO;
use Config\Database;

class AuthController
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    // [POST] /api/login
    public function login()
    {
        $input = json_decode(file_get_contents('php://input'), true);

        $email = htmlspecialchars(trim($input['email'] ?? ''));
        $password = trim($input['password'] ?? '');

        if (!$email || !$password) {
            $this->jsonResponse(400, "Usuario y contraseña requeridos.");
        }

        // Buscar usuario e info de rol mediante JOIN
        $stmt = $this->db->prepare("
            SELECT u.id, u.password_hash, u.estado AS u_estado, u.ver_precios, u.editar_pedidos, u.ver_metricas_recepcion, r.nombre AS rol_nombre, r.estado AS r_estado 
            FROM usuarios u
            INNER JOIN roles r ON u.rol_id = r.id
            WHERE u.email = :email AND u.deleted_at IS NULL
        ");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $this->jsonResponse(401, "Credenciales incorrectas.");
        }

        if ($user['u_estado'] == 0 || $user['r_estado'] == 0) {
            $this->jsonResponse(403, "Cuenta o Rol desactivado. Contacte al administrador.");
        }

        // Validar Password
        if (password_verify($password, $user['password_hash'])) {

            // Prevenir Hijacking
            session_regenerate_id(true);

            // Setear Session Vars
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['rol_nombre'];
            $_SESSION['ver_precios'] = $user['ver_precios'] ?? 0;
            $_SESSION['editar_pedidos'] = $user['editar_pedidos'] ?? 0;
            $_SESSION['ver_metricas_recepcion'] = $user['ver_metricas_recepcion'] ?? 0;
            $_SESSION['last_activity'] = time();

            // Actualizar last_activity BD

            $stmtUpload = $this->db->prepare("UPDATE usuarios SET last_activity = NOW() WHERE id = :id");
            $stmtUpload->execute(['id' => $user['id']]);

            // Responder con token o cookie según la arquitectura elegida para el cliente (Aquí manejado vía Session Cookies)
            http_response_code(200);
            echo json_encode([
                "status" => "success",
                "message" => "Login exitoso",
                "data" => [
                    "user_id" => $user['id'],
                    "role" => $user['rol_nombre']
                ]
            ]);
            exit;
        }
        else {
            $this->jsonResponse(401, "Credenciales incorrectas.");
        }
    }

    // [POST] /api/logout
    public function logout()
    {
        // Unset session variables
        $_SESSION = [];

        // Destroy session cookie
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        // Destroy the session
        session_destroy();

        $this->jsonResponse(200, "Logout exitoso.", "success");
    }

    private function jsonResponse($statusCode, $message, $status = "error")
    {
        http_response_code($statusCode);
        echo json_encode(["status" => $status, "message" => $message]);
        exit;
    }
}