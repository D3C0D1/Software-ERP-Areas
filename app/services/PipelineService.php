<?php
namespace App\Services;

use Exception;
use PDO;
use Config\Database;

class PipelineService
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Paso 1: Mueve un pedido de 'recepcion' a 'proceso', asignándolo al usuario para bloquear la concurrencia.
     */
    public function iniciarProceso($pedidoId, $usuarioId)
    {
        $this->db->beginTransaction();
        try {
            // FOR UPDATE bloquea la fila del pedido mientras dura esta transacción
            $stmt = $this->db->prepare("SELECT area_actual_id, fase_actual, asignado_a_usuario_id FROM pedidos WHERE id = :id FOR UPDATE");
            $stmt->execute(['id' => $pedidoId]);
            $pedido = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$pedido)
                throw new Exception("Pedido no encontrado.");
            if ($pedido['fase_actual'] !== 'recepcion')
                throw new Exception("El pedido ya está en proceso o finalizado en esta área.");
            if ($pedido['asignado_a_usuario_id'])
                throw new Exception("El pedido ya fue tomado por otro operador.");

            // Tomarlo y pasarlo a proceso
            $stmtUpdate = $this->db->prepare("
                UPDATE pedidos 
                SET fase_actual = 'proceso', 
                    asignado_a_usuario_id = :usuario_id, 
                    last_movement_at = NOW() 
                WHERE id = :id
            ");
            $stmtUpdate->execute(['usuario_id' => $usuarioId, 'id' => $pedidoId]);

            $this->registrarMovimiento($pedidoId, $usuarioId, $pedido['area_actual_id'], "Inicia Proceso", "Operador toma el pedido.");

            $this->db->commit();
            return true;
        }
        catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Paso 2: Finaliza la manufactura en el área y lo marca listo para mover.
     */
    public function marcarPreparado($pedidoId, $usuarioId)
    {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("SELECT area_actual_id, fase_actual, asignado_a_usuario_id FROM pedidos WHERE id = :id FOR UPDATE");
            $stmt->execute(['id' => $pedidoId]);
            $pedido = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($pedido['fase_actual'] !== 'proceso')
                throw new Exception("El pedido no está en proceso.");
            // Validar que quien lo termina es quien lo empezó
            if ($pedido['asignado_a_usuario_id'] != $usuarioId)
                throw new Exception("No puedes marcar como preparado un pedido que no tomaste tú.");

            $stmtUpdate = $this->db->prepare("
                UPDATE pedidos 
                SET fase_actual = 'preparado', 
                    last_movement_at = NOW() 
                WHERE id = :id
            ");
            $stmtUpdate->execute(['id' => $pedidoId]);

            $this->registrarMovimiento($pedidoId, $usuarioId, $pedido['area_actual_id'], "Marcado como Preparado", "El operador terminó el trabajo del área.");

            $this->db->commit();
            return true;
        }
        catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Paso 3: Mueve el pedido a la siguiente área productiva cumpliendo la regla de Workflow.
     */
    public function enviarAreaDestino($pedidoId, $usuarioId, $areaDestinoId)
    {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("SELECT area_actual_id, fase_actual FROM pedidos WHERE id = :id FOR UPDATE");
            $stmt->execute(['id' => $pedidoId]);
            $pedido = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$pedido)
                throw new Exception("Pedido no encontrado.");

            if ($pedido['fase_actual'] !== 'preparado')
                throw new Exception("El pedido debe estar en 'Preparado' para poder enviarse a otra área.");

            // Validar que el área destino existe y está activa
            $stmtArea = $this->db->prepare("SELECT id FROM areas WHERE id = :id AND estado = 1 LIMIT 1");
            $stmtArea->execute(['id' => $areaDestinoId]);
            if (!$stmtArea->fetch())
                throw new Exception("El área de destino no existe o está inactiva.");

            // Mover a nueva área, llega en recepción, sin operador asignado
            $stmtUpdate = $this->db->prepare("
                UPDATE pedidos 
                SET area_actual_id = :destino, 
                    fase_actual = 'recepcion', 
                    asignado_a_usuario_id = NULL, 
                    last_movement_at = NOW() 
                WHERE id = :id
            ");
            $stmtUpdate->execute(['destino' => $areaDestinoId, 'id' => $pedidoId]);

            $this->registrarMovimiento($pedidoId, $usuarioId, $pedido['area_actual_id'], "Transferido", "Pedido enviado al área ID: $areaDestinoId");

            $this->db->commit();
            return true;
        }
        catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Retroceso: Devuelve el pedido a un área anterior (debe estar en workflow_transiciones como es_retroceso = 1 opcionalmente).
     */
    public function devolverPedido($pedidoId, $usuarioId, $areaDestinoId, $motivo)
    {
        $this->db->beginTransaction();
        try {
            if (empty(trim($motivo)))
                throw new Exception("El motivo de devolución es obligatorio.");

            $stmt = $this->db->prepare("SELECT area_actual_id FROM pedidos WHERE id = :id FOR UPDATE");
            $stmt->execute(['id' => $pedidoId]);
            $pedido = $stmt->fetch(PDO::FETCH_ASSOC);

            // Registrar devolucion en su tabla de soporte (Opcional según tu requerimiento de tablas de devolucion)
            $stmtDev = $this->db->prepare("INSERT INTO devoluciones (pedido_id, usuario_id, motivo) VALUES (:p, :u, :m)");
            $stmtDev->execute(['p' => $pedidoId, 'u' => $usuarioId, 'm' => $motivo]);

            // Mover hacia atrás
            $stmtUpdate = $this->db->prepare("
                UPDATE pedidos 
                SET area_actual_id = :destino, 
                    fase_actual = 'recepcion', 
                    asignado_a_usuario_id = NULL, 
                    last_movement_at = NOW() 
                WHERE id = :id
            ");
            $stmtUpdate->execute(['destino' => $areaDestinoId, 'id' => $pedidoId]);

            $this->registrarMovimiento($pedidoId, $usuarioId, $pedido['area_actual_id'], "Devolución a ID $areaDestinoId", "Motivo: " . $motivo);

            $this->db->commit();
            return true;
        }
        catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Mueve libremente el pedido entre columnas dentro de la misma área y actualiza el operador si es a 'proceso'
     */
    public function moverFaseLibre($pedidoId, $usuarioId, $nuevaFase)
    {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("SELECT area_actual_id, fase_actual, asignado_a_usuario_id FROM pedidos WHERE id = :id FOR UPDATE");
            $stmt->execute(['id' => $pedidoId]);
            $pedido = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$pedido)
                throw new Exception("Pedido no encontrado.");

            // Si lo movemos a recepcion, se puede borrar el asignado_a_usuario_id.
            $asignadoA = $pedido['asignado_a_usuario_id'];
            if ($nuevaFase === 'recepcion') {
                $asignadoA = null;
            }
            else if ($nuevaFase === 'proceso' && empty($asignadoA)) {
                $asignadoA = $usuarioId;
            }

            $stmtUpdate = $this->db->prepare("
                UPDATE pedidos 
                SET fase_actual = :fase, 
                    asignado_a_usuario_id = :asignado, 
                    last_movement_at = NOW() 
                WHERE id = :id
            ");
            $stmtUpdate->execute([
                'fase' => $nuevaFase,
                'asignado' => $asignadoA,
                'id' => $pedidoId
            ]);

            $this->registrarMovimiento($pedidoId, $usuarioId, $pedido['area_actual_id'], "Traslado libre a $nuevaFase", "Movimiento manual en el Kanban.");

            $this->db->commit();
            return true;
        }
        catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Devuelve el listado del Kanban altamente optimizado con el índice idx_kanban.
     */
    public function obtenerTableroKaban($areaId)
    {
        $where = "p.area_actual_id = :area_id";
        $params = ['area_id' => $areaId];

        if ($areaId == 0) {
            $where = "1=1";
            $params = [];
        }

        $stmt = $this->db->prepare("
            SELECT p.id, p.cliente_nombre, p.descripcion, p.fase_actual, p.asignado_a_usuario_id, p.estado_pago, p.prioridad, p.abonado, p.total, p.cliente_telefono, p.cliente_email, u.nombre AS operador_asignado, p.last_movement_at, a.nombre AS area_nombre, p.fue_editado, p.fecha_entrega_esperada, p.created_at
            FROM pedidos p
            LEFT JOIN usuarios u ON p.asignado_a_usuario_id = u.id
            LEFT JOIN areas a ON p.area_actual_id = a.id
            WHERE $where AND p.estado NOT IN ('completado', 'cancelado')
            ORDER BY p.last_movement_at ASC
        ");
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Revierte un pedido finalizado (estado 'completado') a estado 'pendiente'.
     * Solo para uso administrativo (SuperAdmin).
     */
    public function revertirPedido($pedidoId, $usuarioId)
    {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("SELECT area_actual_id, estado, fase_actual FROM pedidos WHERE id = :id FOR UPDATE");
            $stmt->execute(['id' => $pedidoId]);
            $pedido = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$pedido)
                throw new Exception("Pedido no encontrado.");
            if ($pedido['estado'] !== 'completado')
                throw new Exception("El pedido no está finalizado, no se puede revertir.");

            // Mover a pendiente, lo dejamos en la fase 'preparado' de su última área
            $stmtUpdate = $this->db->prepare("
                UPDATE pedidos 
                SET estado = 'pendiente', 
                    fase_actual = 'preparado', 
                    last_movement_at = NOW() 
                WHERE id = :id
            ");
            $stmtUpdate->execute(['id' => $pedidoId]);

            $this->registrarMovimiento($pedidoId, $usuarioId, $pedido['area_actual_id'], "Reversión Administrativa", "El pedido ha sido reabierto por un administrador.");

            $this->db->commit();
            return true;
        }
        catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    private function registrarMovimiento($pedidoId, $usuarioId, $areaId, $accion, $observaciones = '')
    {
        $stmt = $this->db->prepare("
            INSERT INTO movimientos_pedido (pedido_id, usuario_id, area_id, accion, observaciones) 
            VALUES (:p_id, :u_id, :a_id, :acc, :obs)
        ");
        $stmt->execute([
            'p_id' => $pedidoId,
            'u_id' => $usuarioId,
            'a_id' => $areaId,
            'acc' => $accion,
            'obs' => $observaciones
        ]);

        // LOG AUDITORIA GLOBAL
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $stmtAudit = $this->db->prepare("INSERT INTO auditoria_logs (usuario_id, entidad_tipo, entidad_id, accion, descripcion_accion, ip_address) VALUES (?, 'pedido', ?, 'actualizar', ?, ?)");
        $stmtAudit->execute([$usuarioId, $pedidoId, "$accion - $observaciones", $ip]);
    }
}