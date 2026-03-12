<?php
namespace App\Controllers;

use Config\Database;
use Exception;
use PDO;

class UsuarioController
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function indexView()
    {
        require_once dirname(__DIR__, 2) . '/views/admin_usuarios.php';
        exit;
    }

    public function updateAreas()
    {
        $this->validarMetodoHttp('POST');
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

        try {
            $usuarioId = $input['usuario_id'] ?? null;
            $areasArr = $input['areas'] ?? []; // Array de IDs de áreas que se marcarán

            if (empty($usuarioId))
                throw new Exception("ID de usuario requerido.");

            $this->db->beginTransaction();

            // Eliminar antiguas asignaciones de áreas para este usuario
            $stmtDel = $this->db->prepare("DELETE FROM usuario_areas WHERE usuario_id = :u");
            $stmtDel->execute(['u' => $usuarioId]);

            // Insertar nuevas asignaciones (Iterar checkbox array)
            if (!empty($areasArr) && is_array($areasArr)) {
                $stmtIns = $this->db->prepare("INSERT INTO usuario_areas (usuario_id, area_id) VALUES (:u, :a)");
                foreach ($areasArr as $aId) {
                    $stmtIns->execute(['u' => $usuarioId, 'a' => $aId]);
                }
            }

            // Opcional: Actualizar el Rol, Nombre, ver_precios y editar_pedidos si nos lo mandan
            if (!empty($input['nombre']) && !empty($input['rol_id'])) {
                $verPrecios = isset($input['ver_precios']) ? (int)$input['ver_precios'] : 0;
                $editarPedidos = isset($input['editar_pedidos']) ? (int)$input['editar_pedidos'] : 0;
                $stmtUpd = $this->db->prepare("UPDATE usuarios SET nombre = :n, rol_id = :r, ver_precios = :v, editar_pedidos = :ed WHERE id = :u");
                $stmtUpd->execute([
                    'n' => trim($input['nombre']),
                    'r' => $input['rol_id'],
                    'v' => $verPrecios,
                    'ed' => $editarPedidos,
                    'u' => $usuarioId
                ]);
            }

            $this->db->commit();

            // LOG AUDITORIA
            if (session_status() === PHP_SESSION_NONE)
                session_start();
            $adminId = $_SESSION['user_id'] ?? null;
            $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            $stmtAudit = $this->db->prepare("INSERT INTO auditoria_logs (usuario_id, entidad_tipo, entidad_id, accion, descripcion_accion, ip_address) VALUES (?, 'usuario', ?, 'actualizar', 'Modificó permisos/áreas de usuario', ?)");
            $stmtAudit->execute([$adminId, $usuarioId, $ip]);

            $this->jsonResponse(200, "Permisos de usuario actualizados.");
        }
        catch (Exception $e) {
            $this->db->rollBack();
            $this->jsonResponse(400, "Error: " . $e->getMessage());
        }
    }

    public function crear()
    {
        $this->validarMetodoHttp('POST');
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

        try {
            $nombre = trim($input['nombre'] ?? '');
            $email = trim($input['email'] ?? '');
            $password = trim($input['password'] ?? '');
            $rolId = (int)($input['rol_id'] ?? 3);
            $verPrecios = isset($input['ver_precios']) ? (int)$input['ver_precios'] : 0;
            $editarPedidos = isset($input['editar_pedidos']) ? (int)$input['editar_pedidos'] : 0;
            $crearEnviarPedidos = isset($input['crear_enviar_pedidos']) ? (int)$input['crear_enviar_pedidos'] : 0;
            $devolverPedidos = isset($input['devolver_pedidos']) ? (int)$input['devolver_pedidos'] : 0;
            $verMetricasRecepcion = isset($input['ver_metricas_recepcion']) ? (int)$input['ver_metricas_recepcion'] : 0;
            $areasArr = $input['areas'] ?? [];

            if (empty($nombre) || empty($email) || empty($password))
                throw new Exception("Nombre, email y contraseña son requeridos.");

            // El usuario puede usar un nombre de usuario o email, no validamos formato estricto.

            if (strlen($password) < 6)
                throw new Exception("La contraseña debe tener al menos 6 caracteres.");

            // Verificar si email ya existe
            $stmtChk = $this->db->prepare("SELECT id FROM usuarios WHERE email = :e");
            $stmtChk->execute(['e' => $email]);
            if ($stmtChk->fetch())
                throw new Exception("Ya existe un usuario con ese email.");

            $hash = password_hash($password, PASSWORD_BCRYPT);

            $this->db->beginTransaction();

            $stmtIns = $this->db->prepare(
                "INSERT INTO usuarios (nombre, email, password_hash, rol_id, ver_precios, editar_pedidos, crear_enviar_pedidos, devolver_pedidos, ver_metricas_recepcion, estado) VALUES (:n, :e, :p, :r, :v, :ed, :cp, :dp, :vm, 1)"
            );
            $stmtIns->execute(['n' => $nombre, 'e' => $email, 'p' => $hash, 'r' => $rolId, 'v' => $verPrecios, 'ed' => $editarPedidos, 'cp' => $crearEnviarPedidos, 'dp' => $devolverPedidos, 'vm' => $verMetricasRecepcion]);
            $nuevoId = $this->db->lastInsertId();

            // Asignar areas
            if (!empty($areasArr) && is_array($areasArr)) {
                $stmtArea = $this->db->prepare("INSERT INTO usuario_areas (usuario_id, area_id) VALUES (:u, :a)");
                foreach ($areasArr as $aId) {
                    $stmtArea->execute(['u' => $nuevoId, 'a' => $aId]);
                }
            }

            $this->db->commit();

            // LOG AUDITORIA
            if (session_status() === PHP_SESSION_NONE)
                session_start();
            $adminId = $_SESSION['user_id'] ?? null;
            $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            $stmtAudit = $this->db->prepare("INSERT INTO auditoria_logs (usuario_id, entidad_tipo, entidad_id, accion, descripcion_accion, ip_address) VALUES (?, 'usuario', ?, 'crear', 'Creó nuevo usuario', ?)");
            $stmtAudit->execute([$adminId, $nuevoId, $ip]);

            $this->jsonResponse(201, "Usuario creado correctamente.");
        }
        catch (Exception $e) {
            if ($this->db->inTransaction())
                $this->db->rollBack();
            $this->jsonResponse(400, "Error: " . $e->getMessage());
        }
    }

    /**
     * POST /api/usuarios/editar-admin
     * Edición completa por Admin: nombre, rol, ver_precios, áreas, contraseña (opcional), foto (opcional)
     * Usa multipart/form-data para soportar el archivo de foto.
     */
    public function editarAdmin()
    {
        $this->validarMetodoHttp('POST');
        if (session_status() === PHP_SESSION_NONE)
            session_start();

        $usuarioId = (int)($_POST['usuario_id'] ?? 0);
        $nombre = trim($_POST['nombre'] ?? '');
        $rolId = (int)($_POST['rol_id'] ?? 0);
        $verPrecios = (int)($_POST['ver_precios'] ?? 0);
        $editarPedidos = (int)($_POST['editar_pedidos'] ?? 0);
        $crearEnviarPedidos = (int)($_POST['crear_enviar_pedidos'] ?? 0);
        $devolverPedidos = (int)($_POST['devolver_pedidos'] ?? 0);
        $verMetricasRecepcion = (int)($_POST['ver_metricas_recepcion'] ?? 0);
        $areasArr = json_decode($_POST['areas'] ?? '[]', true) ?: [];
        $password = trim($_POST['nueva_password'] ?? '');

        if (!$usuarioId)
            $this->jsonResponse(400, 'ID de usuario requerido.');
        if (!$nombre)
            $this->jsonResponse(400, 'El nombre es requerido.');

        try {
            $updates = ['nombre = :n', 'rol_id = :r', 'ver_precios = :v', 'editar_pedidos = :ed', 'crear_enviar_pedidos = :cp', 'devolver_pedidos = :dp', 'ver_metricas_recepcion = :vm'];
            $params = [':n' => $nombre, ':r' => $rolId, ':v' => $verPrecios, ':ed' => $editarPedidos, ':cp' => $crearEnviarPedidos, ':dp' => $devolverPedidos, ':vm' => $verMetricasRecepcion, ':id' => $usuarioId];

            // Contraseña opcional
            if ($password !== '') {
                if (strlen($password) < 6)
                    $this->jsonResponse(400, 'La contraseña debe tener al menos 6 caracteres.');
                $updates[] = 'password_hash = :ph';
                $params[':ph'] = password_hash($password, PASSWORD_BCRYPT);
            }

            // Foto de perfil opcional
            if (!empty($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['foto'];
                $maxB = 3 * 1024 * 1024;
                $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                $mime = mime_content_type($file['tmp_name']);

                if ($file['size'] > $maxB)
                    $this->jsonResponse(400, 'La imagen no debe superar 3 MB.');
                if (!in_array($mime, $allowed))
                    $this->jsonResponse(400, 'Solo se permiten JPG, PNG, GIF o WEBP.');

                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $filename = 'u' . $usuarioId . '_' . time() . '.' . $ext;
                $dir = dirname(__DIR__, 2) . '/public/uploads/perfiles/';
                if (!is_dir($dir))
                    mkdir($dir, 0755, true);

                if (!move_uploaded_file($file['tmp_name'], $dir . $filename))
                    $this->jsonResponse(500, 'No se pudo guardar la imagen.');

                $scriptName = dirname($_SERVER['SCRIPT_NAME']);
                $base = str_replace('/public', '', $scriptName);
                if ($base === '/' || $base === '\\')
                    $base = '';
                $fotoUrl = $base . '/public/uploads/perfiles/' . $filename;

                $updates[] = 'foto_perfil = :fp';
                $params[':fp'] = $fotoUrl;
            }

            $this->db->beginTransaction();

            $sql = 'UPDATE usuarios SET ' . implode(', ', $updates) . ' WHERE id = :id';
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            // Áreas
            $this->db->prepare('DELETE FROM usuario_areas WHERE usuario_id = :u')->execute([':u' => $usuarioId]);
            if (!empty($areasArr) && is_array($areasArr)) {
                $stmtA = $this->db->prepare('INSERT INTO usuario_areas (usuario_id, area_id) VALUES (:u, :a)');
                foreach ($areasArr as $aId)
                    $stmtA->execute([':u' => $usuarioId, ':a' => (int)$aId]);
            }

            $this->db->commit();

            $adminId = $_SESSION['user_id'] ?? null;
            $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            $this->db->prepare("INSERT INTO auditoria_logs (usuario_id, entidad_tipo, entidad_id, accion, descripcion_accion, ip_address) VALUES (?, 'usuario', ?, 'actualizar', 'Admin editó usuario', ?)")
                ->execute([$adminId, $usuarioId, $ip]);

            $fotoDevolver = isset($params[':fp']) ? $params[':fp'] : null;
            $this->jsonResponse(200, 'Usuario actualizado correctamente.', ['foto_url' => $fotoDevolver], 'success');

        }
        catch (\Exception $e) {
            if ($this->db->inTransaction())
                $this->db->rollBack();
            $this->jsonResponse(400, 'Error: ' . $e->getMessage());
        }
    }

    public function eliminar()
    {
        $this->validarMetodoHttp('POST');
        if (session_status() === PHP_SESSION_NONE)
            session_start();

        $input = json_decode(file_get_contents('php://input'), true);
        $usuarioId = isset($input['usuario_id']) ? (int)$input['usuario_id'] : 0;

        if (!$usuarioId) {
            $this->jsonResponse(400, 'ID de usuario inválido.');
        }

        if ($usuarioId == $_SESSION['user_id']) {
            $this->jsonResponse(400, 'No puedes eliminar tu propio usuario.');
        }

        try {
            $this->db->beginTransaction();

            // Eliminar asignaciones de areas
            $stmt = $this->db->prepare("DELETE FROM usuario_areas WHERE usuario_id = :u");
            $stmt->execute([':u' => $usuarioId]);

            // Eliminar usuario
            $stmt = $this->db->prepare("DELETE FROM usuarios WHERE id = :u");
            $stmt->execute([':u' => $usuarioId]);

            $this->db->commit();

            $adminId = $_SESSION['user_id'] ?? null;
            $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            $this->db->prepare("INSERT INTO auditoria_logs (usuario_id, entidad_tipo, entidad_id, accion, descripcion_accion, ip_address) VALUES (?, 'usuario', ?, 'eliminar', 'Admin eliminó usuario', ?)")
                ->execute([$adminId, $usuarioId, $ip]);

            $this->jsonResponse(200, 'Usuario eliminado correctamente.', null, 'success');
        }
        catch (\Exception $e) {
            if ($this->db->inTransaction())
                $this->db->rollBack();
            $this->jsonResponse(400, 'Error al eliminar usuario: ' . $e->getMessage());
        }
    }

    private function validarMetodoHttp($metodo)
    {
        if ($_SERVER['REQUEST_METHOD'] !== $metodo)
            $this->jsonResponse(405, 'Método no permitido.');
    }

    private function jsonResponse($code, $message, $data = null, $status = 'error')
    {
        http_response_code($code);
        $resp = ['status' => ($code >= 200 && $code < 300) ? 'success' : 'error', 'message' => $message];
        if ($data !== null)
            $resp['data'] = $data;
        echo json_encode($resp);
        exit;
    }
}