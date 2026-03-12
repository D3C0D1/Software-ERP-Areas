<?php
namespace App\Controllers;

use Config\Database;
use Exception;
use PDO;

class AreaController
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
        // Asegurar que la columna icono sea MEDIUMTEXT (soluciona error "Data too long")
        try {
            $this->db->exec("ALTER TABLE areas MODIFY COLUMN icono MEDIUMTEXT DEFAULT NULL");
        }
        catch (\PDOException $e) {
        // Ignorar si falla por alguna razón (como si la columna no existe aún, 
        // aunque el sidebar se encarga de crearla si falta)
        }
    }

    public function indexView()
    {
        require_once dirname(__DIR__, 2) . '/views/admin_areas.php';
        exit;
    }

    public function store()
    {
        $this->validarMetodoHttp('POST');
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

        try {
            $nombre = trim($input['nombre'] ?? '');
            $descripcion = trim($input['descripcion'] ?? '');
            $icono = trim($input['icono'] ?? '');

            if (empty($nombre))
                throw new Exception("El nombre del área es requerido.");

            $stmt = $this->db->prepare("INSERT INTO areas (nombre, descripcion, icono, orden) VALUES (:n, :d, :i, (SELECT COALESCE(MAX(a.orden), 0) + 1 FROM areas a))");
            $stmt->execute(['n' => $nombre, 'd' => $descripcion, 'i' => $icono]);

            $this->jsonResponse(201, "Área creada correctamente.");
        }
        catch (Exception $e) {
            $this->jsonResponse(400, "Error: " . $e->getMessage());
        }
    }

    public function update()
    {
        $this->validarMetodoHttp('POST');
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

        try {
            $id = $input['id'] ?? null;
            $nombre = trim($input['nombre'] ?? '');
            $descripcion = trim($input['descripcion'] ?? '');
            $icono = trim($input['icono'] ?? '');

            if (empty($id) || empty($nombre))
                throw new Exception("ID y nombre de área son requeridos.");

            $stmt = $this->db->prepare("UPDATE areas SET nombre = :n, descripcion = :d, icono = :i WHERE id = :id");
            $stmt->execute(['n' => $nombre, 'd' => $descripcion, 'i' => $icono, 'id' => $id]);

            $this->jsonResponse(200, "Área actualizada correctamente.");
        }
        catch (Exception $e) {
            $this->jsonResponse(400, "Error: " . $e->getMessage());
        }
    }

    public function updateIcono()
    {
        $this->validarMetodoHttp('POST');
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

        try {
            $id = $input['id'] ?? null;
            $icono = trim($input['icono'] ?? '');

            if (empty($id))
                throw new Exception("ID de área es requerido.");

            $stmt = $this->db->prepare("UPDATE areas SET icono = :i WHERE id = :id");
            $stmt->execute(['i' => $icono, 'id' => $id]);

            $this->jsonResponse(200, "Ícono del área actualizado correctamente.");
        }
        catch (Exception $e) {
            $this->jsonResponse(400, "Error: " . $e->getMessage());
        }
    }

    public function delete()
    {
        $this->validarMetodoHttp('POST');
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

        try {
            $id = $input['id'] ?? null;
            if (empty($id))
                throw new Exception("ID es requerido.");

            // Contar pedidos activos en esta área
            $stmtChk = $this->db->prepare("
                SELECT COUNT(*) as total FROM pedidos 
                WHERE area_actual_id = :id 
                AND estado NOT IN ('cancelado','completado')
            ");
            $stmtChk->execute(['id' => $id]);
            $total = (int)$stmtChk->fetchColumn();

            if ($total > 0) {
                $this->jsonResponse(409, "⛔ Esta área tiene {$total} pedido(s) activo(s). Debes moverlos a otra área antes de poder eliminar esta. Ve al Kanban del área y usa 'Enviar a Área' en cada pedido.");
                return;
            }

            // Desvincular el área del historial (Foreign Key fk_mp_area exige acción manual si es NO ACTION)
            $stmtMx = $this->db->prepare("UPDATE movimientos_pedido SET area_id = NULL WHERE area_id = :id");
            $stmtMx->execute(['id' => $id]);

            // Desvincular pedidos completados/cancelados que pudieran estar todavía amarrados al área
            $stmtPx = $this->db->prepare("UPDATE pedidos SET area_actual_id = NULL WHERE area_actual_id = :id");
            $stmtPx->execute(['id' => $id]);

            // Ahora sí podemos eliminar el área con seguridad
            $stmt = $this->db->prepare("DELETE FROM areas WHERE id = :id");
            $stmt->execute(['id' => $id]);

            $this->jsonResponse(200, "Área eliminada correctamente.");
        }
        catch (Exception $e) {
            $this->jsonResponse(400, "Error: " . $e->getMessage());
        }
    }

    private function validarMetodoHttp($metodo)
    {
        if ($_SERVER['REQUEST_METHOD'] !== $metodo) {
            $this->jsonResponse(405, "Método no permitido.");
        }
    }

    private function jsonResponse($code, $message, $data = null, $status = "error")
    {
        http_response_code($code);
        $response = ["status" => $code >= 200 && $code < 300 ? "success" : "error", "message" => $message];
        if ($data !== null)
            $response['data'] = $data;
        echo json_encode($response);
        exit;
    }
}