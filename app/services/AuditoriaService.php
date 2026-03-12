<?php
namespace App\Services;

use PDO;
use Config\Database;

class AuditoriaService
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Escribe un registro inmutable en auditoria_logs
     */
    public function registrarAccion($usuarioId, $entidadTipo, $entidadId, $accion, $descripcion, $dataAnterior = null, $dataNueva = null)
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

        $stmt = $this->db->prepare("
            INSERT INTO auditoria_logs (usuario_id, entidad_tipo, entidad_id, accion, descripcion_accion, data_anterior, data_nueva, ip_address)
            VALUES (:uid, :etipo, :eid, :accion, :desc, :danterior, :dnueva, :ip)
        ");

        $stmt->execute([
            'uid' => $usuarioId,
            'etipo' => $entidadTipo,
            'eid' => $entidadId,
            'accion' => $accion,
            'desc' => $descripcion,
            'danterior' => $dataAnterior ? json_encode($dataAnterior, JSON_UNESCAPED_UNICODE) : null,
            'dnueva' => $dataNueva ? json_encode($dataNueva, JSON_UNESCAPED_UNICODE) : null,
            'ip' => $ip
        ]);
    }

    /**
     * Trae el histórico de cambios JSON de un pedido o área
     */
    public function obtenerHistorialEntidad($tipo, $id)
    {
        $stmt = $this->db->prepare("
            SELECT a.*, u.nombre AS usuario_responsable
            FROM auditoria_logs a
            LEFT JOIN usuarios u ON a.usuario_id = u.id
            WHERE a.entidad_tipo = :tipo AND a.entidad_id = :id
            ORDER BY a.created_at DESC
        ");
        $stmt->execute(['tipo' => $tipo, 'id' => $id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}