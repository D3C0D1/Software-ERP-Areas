<?php
namespace App\Controllers;

use Config\Database;
use Exception;
use PDO;

class ReportesPedidosController
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Obtiene pedidos para la vista de Reportes (Filtrados por status y fechas)
     */
    public function getListAction()
    {
        try {
            if (session_status() === PHP_SESSION_NONE)
                session_start();
            $estadoType = $_GET['tab'] ?? 'proceso'; // proceso, finalizados, eliminados
            $dias = (int)($_GET['rango_dias'] ?? 0); // 0 = sin filtro

            $query = "
                SELECT 
                    p.id, p.cliente_nombre, p.estado, p.fase_actual,
                    p.total, p.abonado, p.prioridad, p.created_at, p.last_movement_at,
                    a.nombre as area_nombre,
                    u.nombre as usuario_asignado,
                    (SELECT COUNT(*) FROM archivos WHERE entidad_tipo = 'pedido' AND entidad_id = p.id) as adjuntos
                FROM pedidos p
                LEFT JOIN areas a ON p.area_actual_id = a.id
                LEFT JOIN usuarios u ON p.asignado_a_usuario_id = u.id
                WHERE 1=1
            ";

            if ($estadoType === 'proceso') {
                $query .= " AND p.estado IN ('pendiente', 'activo', 'en_proceso', 'pausado') AND p.deleted_at IS NULL";
            }
            elseif ($estadoType === 'finalizados') {
                $query .= " AND p.estado = 'completado' AND p.deleted_at IS NULL";
            }
            elseif ($estadoType === 'eliminados') {
                $query .= " AND (p.estado = 'cancelado' OR p.deleted_at IS NOT NULL)";
            }

            if ($dias > 0) {
                // rango de fechas recientes
                $query .= " AND p.created_at >= DATE_SUB(Now(), INTERVAL $dias DAY)";
            }

            $query .= " ORDER BY p.created_at DESC LIMIT 600";

            $stmt = $this->db->query($query);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            http_response_code(200);
            echo json_encode(['status' => 'success', 'data' => $data]);
        }
        catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    /**
     * Obtiene talles de pedido (seguimiento, tiempo)
     */
    public function getDetallesSeguimientoAction($pedidoId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT p.*, a.nombre as area_nombre, u.nombre as asignado_nombre
                FROM pedidos p
                LEFT JOIN areas a ON p.area_actual_id = a.id
                LEFT JOIN usuarios u ON p.asignado_a_usuario_id = u.id
                WHERE p.id = ?
            ");
            $stmt->execute([$pedidoId]);
            $pedido = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$pedido)
                throw new Exception("Pedido no encontrado.");

            // Historial desde auditoria_logs para auditoría general
            $stmtAuditoria = $this->db->prepare("
                SELECT a.*, r.nombre AS usuario_rol, u.nombre AS usuario_nombre
                FROM auditoria_logs a
                LEFT JOIN usuarios u ON a.usuario_id = u.id
                LEFT JOIN roles r ON u.rol_id = r.id
                WHERE a.entidad_tipo = 'pedido' AND a.entidad_id = ?
                ORDER BY a.created_at DESC
            ");
            $stmtAuditoria->execute([$pedidoId]);
            $auditoria = $stmtAuditoria->fetchAll(PDO::FETCH_ASSOC);

            // Historial desde movimientos_pedido para desglose de tiempos por área
            $stmtMovimientos = $this->db->prepare("
                SELECT m.id, m.accion, m.observaciones, m.created_at, 
                       a.nombre AS area_nombre, u.nombre AS usuario_nombre
                FROM movimientos_pedido m
                LEFT JOIN areas a ON m.area_id = a.id
                LEFT JOIN usuarios u ON m.usuario_id = u.id
                WHERE m.pedido_id = ?
                ORDER BY m.created_at ASC
            ");
            $stmtMovimientos->execute([$pedidoId]);
            $movs = $stmtMovimientos->fetchAll(PDO::FETCH_ASSOC);

            // Calculo de tiempos por área
            $tiemposArea = [];
            $tiempoTotal = 0;
            $ultimaFecha = null;
            $ultimaArea = null;

            foreach ($movs as $m) {
                $fechaActual = strtotime($m['created_at']);
                $areaActual = $m['area_nombre'] ?? 'Recepción inicial';

                if ($ultimaFecha !== null) {
                    $diferenciaSegundos = $fechaActual - $ultimaFecha;
                    $tiempoTotal += $diferenciaSegundos;

                    if (!isset($tiemposArea[$ultimaArea])) {
                        $tiemposArea[$ultimaArea] = 0;
                    }
                    $tiemposArea[$ultimaArea] += $diferenciaSegundos;
                }

                $ultimaFecha = $fechaActual;
                $ultimaArea = $areaActual;
            }

            // Si el pedido no está completado, sumamos el tiempo hasta ahora en el área actual
            if ($pedido['estado'] !== 'completado' && $pedido['estado'] !== 'cancelado' && $ultimaFecha !== null && $ultimaArea !== null) {
                $ahora = time();
                $diferenciaSegundos = $ahora - $ultimaFecha;
                $tiempoTotal += $diferenciaSegundos;

                if (!isset($tiemposArea[$ultimaArea])) {
                    $tiemposArea[$ultimaArea] = 0;
                }
                $tiemposArea[$ultimaArea] += $diferenciaSegundos;
            }

            http_response_code(200);
            echo json_encode(['status' => 'success', 'data' => [
                    'pedido' => $pedido,
                    'auditoria' => $auditoria,
                    'movimientos_area' => $movs,
                    'tiempos_area' => $tiemposArea,
                    'tiempo_total' => $tiempoTotal
                ]]);
        }
        catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    /**
     * Elimina pedidos completamente de la base de datos (con fecha limte)
     */
    public function eliminarPedidosViejosAction()
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST')
                throw new Exception("Method Not Allowed");
            $input = json_decode(file_get_contents('php://input'), true);
            $rango = $input['rango'] ?? null;

            $interval = '1 MONTH';
            if ($rango === '1_year')
                $interval = '1 YEAR';
            elseif ($rango === '1_month')
                $interval = '1 MONTH';
            else
                throw new Exception("Rango inválido.");

            $this->db->beginTransaction();

            $stmtFind = $this->db->query("SELECT id FROM pedidos WHERE created_at <= DATE_SUB(Now(), INTERVAL $interval)");
            $ids = $stmtFind->fetchAll(PDO::FETCH_COLUMN);

            if (count($ids) === 0) {
                $this->db->commit();
                echo json_encode(['status' => 'success', 'message' => "No se encontraron pedidos de hace $interval para eliminar."]);
                return;
            }

            // Eliminar archivos adjuntos (links y fisicos)
            $placeholder = implode(',', array_fill(0, count($ids), '?'));
            $stmtArchivos = $this->db->prepare("SELECT ruta_almacenamiento FROM archivos WHERE entidad_tipo='pedido' AND entidad_id IN ($placeholder)");
            $stmtArchivos->execute($ids);
            while ($file = $stmtArchivos->fetchColumn()) {
                $p = dirname(__DIR__, 2) . '/storage/uploads/' . $file;
                if (file_exists($p))
                    @unlink($p);
            }

            $stmtDelArch = $this->db->prepare("DELETE FROM archivos WHERE entidad_tipo='pedido' AND entidad_id IN ($placeholder)");
            $stmtDelArch->execute($ids);

            $this->db->prepare("DELETE FROM auditoria_logs WHERE entidad_tipo='pedido' AND entidad_id IN ($placeholder)")->execute($ids);

            // Eliminar dependencias u auditoria no es super necesario si la BD tiene cascade, pero asumiendo q no:
            $this->db->prepare("DELETE FROM movimientos_pedido WHERE pedido_id IN ($placeholder)")->execute($ids);
            $this->db->prepare("DELETE FROM pagos WHERE pedido_id IN ($placeholder)")->execute($ids);
            $this->db->prepare("DELETE FROM devoluciones WHERE pedido_id IN ($placeholder)")->execute($ids);

            // Eliminar finalmente el pedido
            $this->db->prepare("DELETE FROM pedidos WHERE id IN ($placeholder)")->execute($ids);

            $this->db->commit();
            http_response_code(200);
            echo json_encode(['status' => 'success', 'message' => count($ids) . " pedido(s) purgados del sistema."]);
        }
        catch (Exception $e) {
            if ($this->db->inTransaction())
                $this->db->rollBack();
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    /**
     * Elimina SOLO archivos de pedidos viejos
     */
    public function purgarArchivosAction()
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST')
                throw new Exception("Method Not Allowed");
            $input = json_decode(file_get_contents('php://input'), true);
            $rango = $input['rango'] ?? null;
            $seleccionPedidos = $input['pedidos_ids'] ?? [];

            $this->db->beginTransaction();

            if (!empty($seleccionPedidos) && is_array($seleccionPedidos)) {
                $placeholder = implode(',', array_fill(0, count($seleccionPedidos), '?'));
                $stmtArchivos = $this->db->prepare("SELECT id, ruta_almacenamiento FROM archivos WHERE entidad_tipo='pedido' AND entidad_id IN ($placeholder)");
                $stmtArchivos->execute($seleccionPedidos);
            }
            else {
                $interval = '1 MONTH';
                if ($rango === '3_months')
                    $interval = '3 MONTH';
                elseif ($rango === '1_month')
                    $interval = '1 MONTH';
                else
                    throw new Exception("Debe seleccionar un rango o pedidos concretos.");

                $stmtArchivos = $this->db->prepare("
                    SELECT a.id, a.ruta_almacenamiento 
                    FROM archivos a 
                    INNER JOIN pedidos p ON (a.entidad_id = p.id AND a.entidad_tipo='pedido')
                    WHERE p.created_at <= DATE_SUB(Now(), INTERVAL $interval)
                ");
                $stmtArchivos->execute();
            }

            $archivos = $stmtArchivos->fetchAll(PDO::FETCH_ASSOC);
            if (count($archivos) === 0) {
                $this->db->commit();
                echo json_encode(['status' => 'success', 'message' => "No se encontraron adjuntos coincidentes."]);
                return;
            }

            $idsToDelete = [];
            foreach ($archivos as $row) {
                $idsToDelete[] = $row['id'];
                $p = dirname(__DIR__, 2) . '/storage/uploads/' . $row['ruta_almacenamiento'];
                if (file_exists($p))
                    @unlink($p);
            }

            $placeholderFiles = implode(',', array_fill(0, count($idsToDelete), '?'));
            $stmtDel = $this->db->prepare("DELETE FROM archivos WHERE id IN ($placeholderFiles)");
            $stmtDel->execute($idsToDelete);

            $this->db->commit();
            http_response_code(200);
            echo json_encode(['status' => 'success', 'message' => count($idsToDelete) . " documento(s) purgados permanentemente."]);
        }
        catch (Exception $e) {
            if ($this->db->inTransaction())
                $this->db->rollBack();
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
}