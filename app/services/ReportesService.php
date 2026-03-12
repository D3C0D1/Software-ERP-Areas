<?php
namespace App\Services;

use PDO;
use Config\Database;

class ReportesService
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Reporte: Lead Time por Rango de Fecha.
     * Exportable, usa índices directos en created_at y last_movement.
     */
    public function obtenerLeadTimeDiario($fechaInicio, $fechaFin)
    {
        $stmt = $this->db->prepare("
            SELECT DATE(created_at) AS dia, 
                   COUNT(id) AS total_gestionados, 
                   AVG(TIMESTAMPDIFF(HOUR, created_at, updated_at)) AS avg_lead_horas
            FROM pedidos
            WHERE estado = 'completado' 
              AND created_at BETWEEN :inicio AND :fin
            GROUP BY DATE(created_at)
        ");
        $stmt->execute(['inicio' => $fechaInicio, 'fin' => $fechaFin]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Reporte: Productividad por Usuario
     * Determina cuántas veces un usuario marcó "Preparado" en su estación de trabajo durante el mes.
     */
    public function obtenerProductividadUsuarios()
    {
        $stmt = $this->db->query("
            SELECT u.nombre AS empleado, a.nombre as area, COUNT(m.id) AS acciones_completadas
            FROM movimientos_pedido m
            INNER JOIN usuarios u ON m.usuario_id = u.id
            INNER JOIN areas a ON m.area_id = a.id
            WHERE m.accion = 'Marcado como Preparado'
              AND MONTH(m.created_at) = MONTH(CURRENT_DATE())
            GROUP BY m.usuario_id, m.area_id
            ORDER BY acciones_completadas DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Reporte: Volumen de Devoluciones Clasificados por Área de Origen.
     */
    public function obtenerDevolucionesPorArea()
    {
        $stmt = $this->db->query("
             SELECT a.nombre AS area_falla, COUNT(d.id) AS total_devoluciones
             FROM devoluciones d
             INNER JOIN pedidos p ON d.pedido_id = p.id
             INNER JOIN areas a ON p.area_actual_id = a.id
             GROUP BY p.area_actual_id
         ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Exportador Genérico Fputcsv a Memoria y Descarga Streaming.
     */
    public function exportarCsv($nombreArchivo, $columnas, $datos)
    {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $nombreArchivo . '.csv');
        $output = fopen('php://output', 'w');

        // Escribe BOM para Excel utf-8
        fputs($output, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));

        // Escribe los encabezados
        fputcsv($output, $columnas, ';'); // Excel suele preferir ';' o puedes usar ','

        // Empuja las filas iterando eficientemente
        foreach ($datos as $fila) {
            fputcsv($output, $fila, ';');
        }

        fclose($output);
        exit;
    }
}