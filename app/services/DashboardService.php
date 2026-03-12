<?php
namespace App\Services;

use PDO;
use Config\Database;

class DashboardService
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Devuelve las 6 métricas clave de Gerencia unificadas para el frontend
     */
    public function getMetricasGerenciales()
    {
        return [
            "pedidos_por_area" => $this->getPedidosPorArea(),
            "riesgo_sla" => $this->getPedidosEnRiesgo(),
            "urgentes" => $this->getUrgentesSinMovimiento(),
            "devoluciones" => $this->getTotalDevoluciones(),
            "empleados" => $this->getEmpleadosActivos(),
            "lead_time" => $this->getLeadTimePromedioArea()
        ];
    }

    /**
     * Pedidos totales agrupados por estaciones.
     * Uso de INDEX (estado, area_actual_id) altamente eficiente, sin traer datos sin procesar.
     */
    private function getPedidosPorArea()
    {
        $stmt = $this->db->query("
            SELECT a.nombre AS area, COUNT(p.id) as total_pedidos 
            FROM areas a
            LEFT JOIN pedidos p ON p.area_actual_id = a.id AND p.estado IN ('pendiente','en_curso')
            GROUP BY a.id
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Calcula qué pedidos en el flujo exceden o están por exceder el tiempo SLA (límite de horas definido en tabla areas).
     * Calculado por MySQL con TIMESTAMPDIFF usando campo last_movement_at.
     */
    private function getPedidosEnRiesgo()
    {
        // Cuellos de botella donde last_movement_at fue hace X horas mayor al SLA.
        $stmt = $this->db->query("
            SELECT p.id as pedido_id, p.area_actual_id, a.nombre, a.sla_horas, 
                   TIMESTAMPDIFF(HOUR, p.last_movement_at, NOW()) AS horas_estancado
            FROM pedidos p
            INNER JOIN areas a ON p.area_actual_id = a.id
            WHERE p.estado IN ('pendiente', 'en_curso')
              AND TIMESTAMPDIFF(HOUR, p.last_movement_at, NOW()) > (a.sla_horas * 0.8) -- Supera el 80% del SLA
            ORDER BY horas_estancado DESC
            LIMIT 20
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getUrgentesSinMovimiento()
    {
        // Muestra urgentes (+72h sin tocarse o donde fecha_entrega es inminente).
        $stmt = $this->db->query("
            SELECT id, last_movement_at, fecha_entrega_esperada 
            FROM pedidos 
            WHERE estado != 'completado'
            AND (last_movement_at < DATE_SUB(NOW(), INTERVAL 72 HOUR) 
                 OR fecha_entrega_esperada <= DATE_ADD(CURDATE(), INTERVAL 2 DAY))
            LIMIT 50
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getTotalDevoluciones()
    {
        $stmt = $this->db->query("
            SELECT COUNT(*) AS total_mensual 
            FROM devoluciones 
            WHERE MONTH(created_at) = MONTH(CURRENT_DATE())
        ");
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res['total_mensual'] ?? 0;
    }

    private function getEmpleadosActivos()
    {
        // Operadores que se han movido en los ultimos 10 minutos (600 seg)
        $stmt = $this->db->query("
            SELECT COUNT(id) AS empleados_online 
            FROM usuarios 
            WHERE last_activity > DATE_SUB(NOW(), INTERVAL 10 MINUTE)
        ");
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res['empleados_online'] ?? 0;
    }

    private function getLeadTimePromedioArea()
    {
        // En lugar de calcular cada fila por la gigantesca tabla de historiales (movimientos_pedido),
        // mantenemos las estimaciones usando funciones agregadas MySQL.
        return "Cálculo en Batch nocturno o BI sugerido para evitar bloqueo analítico.";
    }

}