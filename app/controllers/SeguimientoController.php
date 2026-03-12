<?php
namespace App\Controllers;

use Config\Database;
use Exception;
use PDO;

class SeguimientoController
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getProgresoAction($token)
    {
        try {
            $token = trim((string)$token);
            if (empty($token)) {
                $this->jsonResponse(400, "Token inválido.");
            }

            // Podemos buscar por token_seguimiento o ID como fallback
            $stmt = $this->db->prepare("
                SELECT p.id, p.cliente_nombre, p.estado, p.fase_actual, p.area_actual_id, a.nombre as area_nombre, 
                       p.estado_pago, p.total, p.abonado, p.created_at
                FROM pedidos p
                LEFT JOIN areas a ON p.area_actual_id = a.id
                WHERE p.token_seguimiento = :token OR p.id = :idFallback
            ");
            $stmt->execute(['token' => $token, 'idFallback' => is_numeric($token) ? intval($token) : 0]);
            $pedido = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$pedido) {
                $this->jsonResponse(404, "Pedido no encontrado.");
            }

            // Obtenemos todas las áreas activas
            $stmtAreas = $this->db->query("SELECT id, nombre, icono FROM areas WHERE estado = 1 ORDER BY orden ASC");
            $areas = $stmtAreas->fetchAll(PDO::FETCH_ASSOC);

            $this->jsonResponse(200, "OK", [
                'pedido' => $pedido,
                'areas' => $areas
            ], 'success');

        }
        catch (Exception $e) {
            $this->jsonResponse(500, "Error del servidor: " . $e->getMessage());
        }
    }

    private function jsonResponse($code, $message, $data = null, $status = "error")
    {
        http_response_code($code);
        $response = ["status" => $status, "message" => $message];
        if ($data !== null)
            $response['data'] = $data;
        echo json_encode($response);
        exit;
    }

    public function registrarAperturaCliente($token)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Evitar logs repetitivos en la misma sesión
        if (!isset($_SESSION["tracked_token_$token"])) {
            $stmt = $this->db->prepare("SELECT id FROM pedidos WHERE token_seguimiento = :token");
            $stmt->execute(['token' => $token]);
            $pedidoId = $stmt->fetchColumn();

            if ($pedidoId) {
                $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';
                $stmtLog = $this->db->prepare("
                    INSERT INTO auditoria_logs (usuario_id, entidad_tipo, entidad_id, accion, descripcion_accion, ip_address)
                    VALUES (NULL, 'pedido', :pid, 'abrio_tracking', 'El cliente abrió la página de seguimiento / guía', :ip)
                ");
                $stmtLog->execute(['pid' => $pedidoId, 'ip' => $ip]);
                $_SESSION["tracked_token_$token"] = true;
            }
        }
    }
}