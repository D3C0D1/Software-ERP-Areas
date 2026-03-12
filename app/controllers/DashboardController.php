<?php
namespace App\Controllers;

use App\Services\DashboardService;
use Config\Database;
use Exception;
use PDO;

class DashboardController
{
    private $dashboardService;
    private $db;

    public function __construct()
    {
        $this->dashboardService = new DashboardService();
        $this->db = Database::getInstance();
    }

    /**
     * [GET] /api/dashboard/metricas  — métricas gerenciales globales (legado)
     */
    public function getMetricasAction()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            exit;
        }
        try {
            $metricas = $this->dashboardService->getMetricasGerenciales();
            http_response_code(200);
            echo json_encode(["status" => "success", "message" => "OK", "data" => $metricas]);
        }
        catch (Exception $e) {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => $e->getMessage()]);
        }
    }

    /**
     * [GET] /api/dashboard/areas
     * Retorna todas las áreas activas con sus conteos de pedidos por fase.
     */
    public function getMetricasAreasAction()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            exit;
        }
        try {
            $stmt = $this->db->query("
                SELECT
                    a.id,
                    a.nombre,
                    a.orden,
                    COALESCE(SUM(CASE WHEN p.fase_actual = 'recepcion' AND p.estado NOT IN ('cancelado','completado') THEN 1 ELSE 0 END), 0) AS cnt_recepcion,
                    COALESCE(SUM(CASE WHEN p.fase_actual = 'proceso'   AND p.estado NOT IN ('cancelado','completado') THEN 1 ELSE 0 END), 0) AS cnt_proceso,
                    COALESCE(SUM(CASE WHEN p.fase_actual = 'preparado' AND p.estado NOT IN ('cancelado','completado') THEN 1 ELSE 0 END), 0) AS cnt_preparado,
                    COALESCE(SUM(CASE WHEN p.estado = 'completado' THEN 1 ELSE 0 END), 0) AS cnt_completado
                FROM areas a
                LEFT JOIN pedidos p ON p.area_actual_id = a.id
                WHERE a.estado = 1
                GROUP BY a.id, a.nombre, a.orden
                ORDER BY a.orden ASC
            ");
            $areas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $globalStmt = $this->db->query("
                SELECT 
                    (SELECT COUNT(*) FROM pedidos WHERE DATE(created_at) = CURDATE()) as pedidos_dia,
                    (SELECT COUNT(*) FROM pedidos WHERE YEAR(created_at) = YEAR(CURDATE()) AND MONTH(created_at) = MONTH(CURDATE())) as pedidos_mes,
                    (SELECT COUNT(*) FROM pedidos) as pedidos_total,
                    (SELECT COUNT(*) FROM pedidos WHERE estado = 'cancelado') as pedidos_eliminados,
                    (SELECT COUNT(*) FROM pedidos WHERE entregado = 1 AND estado != 'cancelado') as pedidos_entregados,
                    (SELECT COUNT(*) FROM pedidos WHERE estado != 'cancelado' AND (entregado = 0 OR entregado IS NULL)) as pedidos_no_entregados,
                    (SELECT COUNT(*) FROM pedidos WHERE estado = 'completado' AND (entregado = 0 OR entregado IS NULL)) as pedidos_fin_no_entregados,
                    (SELECT COUNT(*) FROM pedidos WHERE estado_pago = 'pago_completo' AND estado != 'cancelado') as pedidos_pagados,
                    (SELECT COUNT(*) FROM pedidos WHERE estado_pago = 'no_pago' AND estado != 'cancelado') as pedidos_no_pagados,
                    (SELECT COUNT(*) FROM pedidos WHERE estado_pago = 'abono' AND estado != 'cancelado') as pedidos_abonados,
                    (SELECT COUNT(*) FROM pedidos WHERE fecha_entrega_esperada IS NOT NULL AND fecha_entrega_esperada < CURDATE() AND estado NOT IN ('completado','cancelado')) as pedidos_caducados,
                    (SELECT COUNT(*) FROM pedidos WHERE fecha_entrega_esperada IS NOT NULL AND fecha_entrega_esperada >= CURDATE() AND TIMESTAMPDIFF(HOUR, NOW(), CONCAT(fecha_entrega_esperada,' 23:59:59')) <= 12 AND estado NOT IN ('completado','cancelado')) as pedidos_por_caducar
            ");
            $globalMetrics = $globalStmt->fetch(PDO::FETCH_ASSOC);

            $meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
            $globalMetrics['mes_actual_nombre'] = $meses[date('n') - 1];

            echo json_encode(["status" => "success", "data" => $areas, "global" => $globalMetrics]);
        }
        catch (Exception $e) {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => $e->getMessage()]);
        }
    }

    /**
     * [GET] /api/dashboard/area/{id}/pedidos?fase=recepcion|preparado|completado
     * Retorna los pedidos de un área filtrados por fase.
     */
    public function getPedidosAreaAction($areaId)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            exit;
        }
        try {
            $areaId = intval($areaId);
            $fase = $_GET['fase'] ?? 'recepcion';

            // Fases válidas
            $fasesValidas = ['recepcion', 'proceso', 'preparado', 'completado'];
            if (!in_array($fase, $fasesValidas))
                $fase = 'recepcion';

            if ($fase === 'completado') {
                $sql = "
                    SELECT p.id, p.cliente_nombre, p.cliente_telefono, p.cliente_email,
                           p.estado_pago, p.prioridad, p.total, p.abonado,
                           p.fase_actual, p.estado, p.descripcion, p.fue_editado,
                           p.created_at, p.last_movement_at,
                           a.nombre AS area_nombre
                    FROM pedidos p
                    JOIN areas a ON a.id = p.area_actual_id
                    WHERE p.area_actual_id = :area
                      AND p.estado = 'completado'
                    ORDER BY p.last_movement_at DESC
                    LIMIT 100
                ";
            }
            else {
                $sql = "
                    SELECT p.id, p.cliente_nombre, p.cliente_telefono, p.cliente_email,
                           p.estado_pago, p.prioridad, p.total, p.abonado,
                           p.fase_actual, p.estado, p.descripcion, p.fue_editado,
                           p.created_at, p.last_movement_at,
                           a.nombre AS area_nombre
                    FROM pedidos p
                    JOIN areas a ON a.id = p.area_actual_id
                    WHERE p.area_actual_id = :area
                      AND p.fase_actual = :fase
                      AND p.estado NOT IN ('cancelado','completado')
                    ORDER BY p.id DESC
                    LIMIT 100
                ";
            }

            $stmt = $this->db->prepare($sql);
            $params = ['area' => $areaId];
            if ($fase !== 'completado')
                $params['fase'] = $fase;
            $stmt->execute($params);
            $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode(["status" => "success", "data" => $pedidos]);
        }
        catch (Exception $e) {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => $e->getMessage()]);
        }
    }
}