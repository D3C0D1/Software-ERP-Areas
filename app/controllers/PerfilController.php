<?php
namespace App\Controllers;

use Config\Database;
use Exception;

class PerfilController
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
        if (session_status() === PHP_SESSION_NONE)
            session_start();
    }

    /**
     * POST /api/perfil/actualizar
     * Campos multipart: nueva_password (opcional), foto (opcional file)
     */
    public function actualizar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(405, 'Método no permitido.');
        }

        if (!isset($_SESSION['user_id'])) {
            $this->json(401, 'Acceso denegado. tiempo expirado.');
        }

        $userId = (int)$_SESSION['user_id'];
        $updates = [];
        $params = [];
        $nombreActualizado = false;

        // --- Cambio de nombre ---
        $nuevoNombre = trim($_POST['nombre'] ?? '');
        if ($nuevoNombre !== '') {
            if (strlen($nuevoNombre) > 50) {
                $this->json(400, 'El nombre no puede exceder 50 caracteres.');
            }
            $updates[] = 'nombre = :nombre';
            $params[':nombre'] = $nuevoNombre;
            $nombreActualizado = true;
        }

        // --- Cambio de contraseña ---
        $nuevaPassword = trim($_POST['nueva_password'] ?? '');
        if ($nuevaPassword !== '') {
            if (strlen($nuevaPassword) < 6) {
                $this->json(400, 'La contraseña debe tener al menos 6 caracteres.');
            }
            $updates[] = 'password_hash = :ph';
            $params[':ph'] = password_hash($nuevaPassword, PASSWORD_BCRYPT);
        }

        // --- Foto de perfil ---
        if (!empty($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['foto'];
            $maxBytes = 3 * 1024 * 1024; // 3 MB
            $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $mime = mime_content_type($file['tmp_name']);

            if ($file['size'] > $maxBytes) {
                $this->json(400, 'La imagen no debe superar 3 MB.');
            }
            if (!in_array($mime, $allowed)) {
                $this->json(400, 'Solo se permiten imágenes JPG, PNG, GIF o WEBP.');
            }

            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'u' . $userId . '_' . time() . '.' . strtolower($ext);
            $uploadDir = dirname(__DIR__, 2) . '/public/uploads/perfiles/';
            $dest = $uploadDir . $filename;

            if (!is_dir($uploadDir))
                mkdir($uploadDir, 0755, true);

            if (!move_uploaded_file($file['tmp_name'], $dest)) {
                $this->json(500, 'No se pudo guardar la imagen.');
            }

            // Calcular URL relativa (basePath neutral)
            $scriptName = dirname($_SERVER['SCRIPT_NAME']);
            $base = str_replace('/public', '', $scriptName);
            if ($base === '/' || $base === '\\')
                $base = '';
            $fotoUrl = $base . '/public/uploads/perfiles/' . $filename;

            $updates[] = 'foto_perfil = :fp';
            $params[':fp'] = $fotoUrl;
        }

        if (empty($updates)) {
            $this->json(400, 'No hay cambios que guardar.');
        }

        $params[':id'] = $userId;
        $sql = 'UPDATE usuarios SET ' . implode(', ', $updates) . ' WHERE id = :id';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        // Devolver nueva URL de foto si cambió, y flag de nombre
        $fotoDev = isset($params[':fp']) ? $params[':fp'] : null;
        $this->json(200, 'Perfil actualizado correctamente.', [
            'foto_url' => $fotoDev,
            'nombre_actualizado' => $nombreActualizado,
            'nombre' => $nombreActualizado ? $nuevoNombre : null
        ], 'success');
    }


    private function json($code, $message, $data = null, $status = 'error')
    {
        http_response_code($code);
        $r = ['status' => ($code >= 200 && $code < 300) ? 'success' : 'error', 'message' => $message];
        if ($data !== null)
            $r['data'] = $data;
        echo json_encode($r);
        exit;
    }
}