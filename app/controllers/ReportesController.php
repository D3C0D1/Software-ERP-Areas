<?php
namespace App\Controllers;

use Config\Database;
use Exception;
use PDO;

class ReportesController
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getMovimientosAction()
    {
        try {
            // Filtros opcionales
            $filtroAccion = $_GET['accion'] ?? '';
            $filtroUsuario = $_GET['usuario'] ?? '';
            $filtroPedido = $_GET['pedido_id'] ?? '';

            $query = "
                SELECT 
                    a.id,
                    a.usuario_id,
                    u.nombre AS usuario_nombre,
                    r.nombre AS usuario_rol,
                    a.entidad_tipo,
                    a.entidad_id,
                    a.accion,
                    a.descripcion_accion,
                    a.data_anterior,
                    a.data_nueva,
                    a.ip_address,
                    a.created_at,
                    p.cliente_nombre
                FROM auditoria_logs a
                LEFT JOIN usuarios u ON a.usuario_id = u.id
                LEFT JOIN roles r ON u.rol_id = r.id
                LEFT JOIN pedidos p ON (a.entidad_tipo = 'pedido' AND a.entidad_id = p.id)
                WHERE 1=1
            ";

            $params = [];

            if ($filtroAccion !== '') {
                $query .= " AND a.accion = :accion";
                $params['accion'] = $filtroAccion;
            }

            if ($filtroUsuario !== '') {
                $query .= " AND u.nombre LIKE :usuario";
                $params['usuario'] = '%' . $filtroUsuario . '%';
            }

            if ($filtroPedido !== '') {
                $query .= " AND a.entidad_id = :pedido AND a.entidad_tipo = 'pedido'";
                $params['pedido'] = $filtroPedido;
            }

            $query .= " ORDER BY a.created_at DESC LIMIT 500";

            $stmt = $this->db->prepare($query);
            $stmt->execute($params);

            $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

            http_response_code(200);
            echo json_encode(['status' => 'success', 'data' => $logs]);
        }
        catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
}