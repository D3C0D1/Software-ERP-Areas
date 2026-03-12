<?php
namespace App\Controllers;

use Config\Database;
use Exception;
use PDO;

class RecepcionController
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
        if (session_status() === PHP_SESSION_NONE)
            session_start();
    }

    /**
     * Crea un nuevo pedido siempre en la primera área activa (Recepción).
     */
    public function store()
    {
        $this->validarMetodoHttp('POST');
        $input = !empty($_POST) ? $_POST : (json_decode(file_get_contents('php://input'), true) ?? []);

        try {
            $cliente = trim($input['cliente_nombre'] ?? '');
            $email = trim($input['cliente_email'] ?? '');
            $telefono = trim($input['cliente_telefono'] ?? '');
            $descripcion = trim($input['descripcion'] ?? '');
            $estadoPago = $input['estado_pago'] ?? 'no_pago';
            $prioridad = $input['prioridad'] ?? 'normal';
            $abonado = floatval($input['abonado'] ?? 0);
            $total = floatval($input['total'] ?? 0);
            $fecha_entrega_esperada = !empty($input['fecha_entrega_esperada']) ? $input['fecha_entrega_esperada'] : null;

            // Auto-complete payment validation
            if ($total > 0 && $abonado >= $total) {
                $estadoPago = 'pago_completo';
                $abonado = $total;
            }
            elseif ($abonado > 0 && $estadoPago === 'no_pago') {
                $estadoPago = 'abono';
            }

            if (empty($cliente))
                throw new Exception("El nombre del cliente es obligatorio.");

            $metodoPago = in_array($input['metodo_pago'] ?? 'efectivo', ['efectivo', 'transferencia']) ? ($input['metodo_pago'] ?? 'efectivo') : 'efectivo';

            // El pedido inicia en recepción, sin área de producción asignada.
            $areaId = null;

            $this->db->beginTransaction();

            $token = substr(str_shuffle("ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789"), 0, 6);

            $stmt = $this->db->prepare("
                INSERT INTO pedidos (cliente_nombre, cliente_email, cliente_telefono, descripcion, area_actual_id, fase_actual, estado_pago, prioridad, abonado, total, token_seguimiento, fecha_entrega_esperada, fecha_pago, metodo_pago)
                VALUES (:cliente, :email, :tel, :desc, :area_id, 'recepcion', :estado_pago, :prioridad, :abonado, :total, :token, :fecha_entrega_esperada, :fecha_pago, :metodo_pago)
            ");
            $fecha_pago = ($estadoPago === 'pago_completo' || $estadoPago === 'abono') ? date('Y-m-d H:i:s') : null;
            $stmt->execute([
                'cliente' => $cliente,
                'email' => $email,
                'tel' => $telefono,
                'desc' => $descripcion,
                'area_id' => $areaId,
                'estado_pago' => $estadoPago,
                'prioridad' => $prioridad,
                'abonado' => $abonado,
                'total' => $total,
                'token' => $token,
                'fecha_entrega_esperada' => $fecha_entrega_esperada,
                'fecha_pago' => $fecha_pago,
                'metodo_pago' => $metodoPago
            ]);

            $pedidoId = $this->db->lastInsertId();
            $usuarioId = $_SESSION['user_id'] ?? null;

            // Inserta en historial_pagos si hay pago inicial
            if ($abonado > 0) {
                $stmtHP = $this->db->prepare("INSERT INTO historial_pagos (pedido_id, monto, metodo_pago, usuario_id, observacion) VALUES (:p, :m, :met, :u, 'Abono inicial generado en Recepción')");
                $stmtHP->execute(['p' => $pedidoId, 'm' => $abonado, 'met' => $metodoPago, 'u' => $usuarioId]);
            }

            // Registrar movimiento: si area es null (recepción sin área) se omite area_id
            if ($areaId !== null) {
                $stmtLog = $this->db->prepare("
                    INSERT INTO movimientos_pedido (pedido_id, usuario_id, area_id, accion, observaciones)
                    VALUES (:p, :u, :a, 'Creado', 'Pedido ingresado desde Recepción')
                ");
                $stmtLog->execute(['p' => $pedidoId, 'u' => $usuarioId, 'a' => $areaId]);
            }
            else {
                $stmtLog = $this->db->prepare("
                    INSERT INTO movimientos_pedido (pedido_id, usuario_id, accion, observaciones)
                    VALUES (:p, :u, 'Creado', 'Pedido ingresado desde Recepción')
                ");
                $stmtLog->execute(['p' => $pedidoId, 'u' => $usuarioId]);
            }

            $this->db->commit();

            // Enviar SMS si está marcado
            $sendSms = !empty($input['send_sms']);
            if ($sendSms && !empty($telefono)) {
                $stmtCfg = $this->db->query("SELECT clave, valor FROM configuracion WHERE clave IN ('sms_crear', 'empresa_nombre')");
                $cfg = $stmtCfg->fetchAll(PDO::FETCH_KEY_PAIR);
                $plantilla = $cfg['sms_crear'] ?? 'Hola {nombre}, tu pedido {link_seguimiento} ha sido creado. Gracias por confiar en {empresa}';
                $empresa = $cfg['empresa_nombre'] ?? 'Banner';

                // Formatear mensaje
                $basePath = preg_replace('/\/public\/index\.php$/i', '', $_SERVER['SCRIPT_NAME']);
                $enlace = $_SERVER['HTTP_HOST'] . $basePath . "/seguimiento.php?token=" . $token;
                $numeroPedido = 'PED-' . str_pad($pedidoId, 4, '0', STR_PAD_LEFT);
                $mensaje = str_replace(
                ['{nombre}', '{numero_pedido}', '{link_seguimiento}', '{empresa}'],
                [$cliente, $numeroPedido, $enlace, $empresa],
                    $plantilla
                );

                require_once __DIR__ . '/../services/OnurixService.php';
                $onurix = new \App\Services\OnurixService();
                $onurix->enviarSMS($telefono, $mensaje);
            }

            // Enviar WhatsApp por plantilla si está marcado y activo globalmente
            $sendWhatsapp = !empty($input['send_whatsapp']);
            if ($sendWhatsapp && !empty($telefono)) {
                $stmtWaCfg = $this->db->query("SELECT clave, valor FROM configuracion WHERE clave IN ('whatsapp_activo','empresa_nombre')");
                $waCfg = $stmtWaCfg->fetchAll(PDO::FETCH_KEY_PAIR);
                $waActivo = ($waCfg['whatsapp_activo'] ?? '1') === '1';

                if ($waActivo) {
                    $basePath2 = preg_replace('/\/public\/index\.php$/i', '', $_SERVER['SCRIPT_NAME']);
                    $enlaceWa = $_SERVER['HTTP_HOST'] . $basePath2 . '/seguimiento.php?token=' . $token;

                    if (!isset($onurix)) {
                        require_once __DIR__ . '/../services/OnurixService.php';
                        $onurix = new \App\Services\OnurixService();
                    }

                    // Llama con nombre del cliente y link de seguimiento
                    // (se mapean a las variables "nombre" y "link" de la plantilla META)
                    $onurix->enviarWhatsApp($telefono, $cliente, $enlaceWa);
                }
            }

            // --- TRATAMIENTO DE ARCHIVOS ---
            $archivosLog = "";
            if (!empty($_FILES['archivos']['name'][0])) {
                $uploadDir = dirname(__DIR__, 2) . '/storage/uploads/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $stmtArchivo = $this->db->prepare("
                    INSERT INTO archivos (entidad_tipo, entidad_id, nombre_archivo, ruta_almacenamiento, tipo_mime, subido_por)
                    VALUES ('pedido', :id, :nombre, :ruta, :mime, :user)
                ");

                $totalFiles = count($_FILES['archivos']['name']);
                for ($i = 0; $i < $totalFiles; $i++) {
                    if ($_FILES['archivos']['error'][$i] === 0) {
                        $nombreOriginal = $_FILES['archivos']['name'][$i];
                        $tmpName = $_FILES['archivos']['tmp_name'][$i];
                        $mime = $_FILES['archivos']['type'][$i];
                        $peso = $_FILES['archivos']['size'][$i];

                        $ext = pathinfo($nombreOriginal, PATHINFO_EXTENSION);
                        $nombreLimpio = preg_replace("/[^a-zA-Z0-9.\-_]/", "", $nombreOriginal);
                        $nombreDestino = 'recep_' . $pedidoId . '_' . time() . '_' . $i . '.' . $ext;
                        $rutaDestino = $uploadDir . $nombreDestino;

                        if (move_uploaded_file($tmpName, $rutaDestino)) {
                            $stmtArchivo->execute([
                                'id' => $pedidoId,
                                'nombre' => $nombreLimpio,
                                'ruta' => $nombreDestino,
                                'mime' => $mime,
                                'user' => $usuarioId
                            ]);
                            $archivosLog .= $nombreLimpio . " ";
                        }
                    }
                }
            }

            if ($archivosLog !== "") {
                $stmtLogAttach = $this->db->prepare("
                    INSERT INTO movimientos_pedido (pedido_id, usuario_id, area_id, accion, observaciones)
                    VALUES (:p, :u, :a, 'Adjunto', :obs)
                ");
                $stmtLogAttach->execute(['p' => $pedidoId, 'u' => $usuarioId, 'a' => $areaId, 'obs' => 'Archivos adjuntados al crear: ' . $archivosLog]);
            }

            // LOG AUDITORIA
            $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            $stmtAudit = $this->db->prepare("INSERT INTO auditoria_logs (usuario_id, entidad_tipo, entidad_id, accion, descripcion_accion, ip_address) VALUES (?, 'pedido', ?, 'crear', 'Creó un pedido nuevo en Recepción', ?)");
            $stmtAudit->execute([$usuarioId, $pedidoId, $ip]);

            $this->jsonResponse(201, "Pedido creado exitosamente en Recepción.", ['pedido_id' => $pedidoId, 'token' => $token], 'success');

        }
        catch (Exception $e) {
            if ($this->db->inTransaction())
                $this->db->rollBack();
            $this->jsonResponse(400, $e->getMessage());
        }
    }

    /**
     * Actualiza los datos de un pedido existente.
     */
    public function update()
    {
        $this->validarMetodoHttp('POST');
        // Solo Admin puede editar pedidos desde Recepción
        if (($_SESSION['role'] ?? '') !== 'Admin') {
            $this->jsonResponse(403, 'No tienes permiso para editar pedidos.');
            return;
        }
        $input = !empty($_POST) ? $_POST : (json_decode(file_get_contents('php://input'), true) ?? []);

        try {
            $id = intval($input['pedido_id'] ?? 0);
            $cliente = trim($input['cliente_nombre'] ?? '');
            $email = trim($input['cliente_email'] ?? '');
            $telefono = trim($input['cliente_telefono'] ?? '');
            $descripcion = trim($input['descripcion'] ?? '');
            $estadoPago = $input['estado_pago'] ?? 'no_pago';
            $prioridad = $input['prioridad'] ?? 'normal';
            $abonado = floatval($input['abonado'] ?? 0);
            $total = floatval($input['total'] ?? 0);
            $fecha_entrega_esperada = !empty($input['fecha_entrega']) ? $input['fecha_entrega'] : (!empty($input['fecha_entrega_esperada']) ? $input['fecha_entrega_esperada'] : null);

            // Auto-complete payment validation
            if ($total > 0 && $abonado >= $total) {
                $estadoPago = 'pago_completo';
                $abonado = $total;
            }
            elseif ($abonado > 0 && $estadoPago === 'no_pago') {
                $estadoPago = 'abono';
            }

            if (!$id)
                throw new Exception("ID de pedido inválido.");
            if (empty($cliente))
                throw new Exception("El nombre del cliente es obligatorio.");

            $stmtOld = $this->db->prepare("SELECT abonado, estado_pago FROM pedidos WHERE id = :id");
            $stmtOld->execute(['id' => $id]);
            $oldPedido = $stmtOld->fetch(PDO::FETCH_ASSOC);

            $queryUpdates = "cliente_nombre = :cliente, cliente_email = :email, cliente_telefono = :tel, descripcion = :desc, prioridad = :prioridad, fecha_entrega_esperada = :fecha";
            $params = [
                'cliente' => $cliente,
                'email' => $email,
                'tel' => $telefono,
                'desc' => $descripcion,
                'prioridad' => $prioridad,
                'fecha' => $fecha_entrega_esperada,
                'id' => $id
            ];

            if (isset($input['estado_pago']) || ($total > 0 && $abonado >= $total)) {
                $queryUpdates .= ", estado_pago = :estado_pago";
                $params['estado_pago'] = $estadoPago;
            }
            if (isset($input['abonado'])) {
                $queryUpdates .= ", abonado = :abonado";
                $params['abonado'] = $abonado;
            }
            if (isset($input['total'])) {
                $queryUpdates .= ", total = :total";
                $params['total'] = $total;
            }

            if ($oldPedido) {
                if ($abonado != $oldPedido['abonado'] || ($estadoPago != 'no_pago' && $estadoPago != $oldPedido['estado_pago'])) {
                    $queryUpdates .= ", fecha_pago = NOW()";
                }
            }

            $queryUpdates .= ", fue_editado = 1";

            $stmt = $this->db->prepare("UPDATE pedidos SET $queryUpdates WHERE id = :id");
            $stmt->execute($params);

            $cambios = false;
            if ($stmt->rowCount() > 0)
                $cambios = true;
            $usuarioId = $_SESSION['user_id'] ?? null;

            // --- ELIMINACION DE ARCHIVOS ---
            $archivosEliminados = json_decode($input['archivos_eliminados'] ?? '[]', true);
            if (!empty($archivosEliminados) && is_array($archivosEliminados)) {
                $stmtDelArch = $this->db->prepare("DELETE FROM archivos WHERE id = ? AND entidad_id = ? AND entidad_tipo = 'pedido'");
                foreach ($archivosEliminados as $aid) {
                    $stmtDelArch->execute([$aid, $id]);
                    $cambios = true;
                }
            }

            // --- SUBIDA DE NUEVOS ARCHIVOS ---
            $archivosLog = "";
            if (!empty($_FILES['archivos']['name'][0])) {
                $uploadDir = dirname(__DIR__, 2) . '/storage/uploads/';
                if (!is_dir($uploadDir))
                    mkdir($uploadDir, 0755, true);

                $stmtArchivo = $this->db->prepare("
                    INSERT INTO archivos (entidad_tipo, entidad_id, nombre_archivo, ruta_almacenamiento, tipo_mime, subido_por)
                    VALUES ('pedido', :id, :nombre, :ruta, :mime, :user)
                ");

                $totalFiles = count($_FILES['archivos']['name']);
                for ($i = 0; $i < $totalFiles; $i++) {
                    if ($_FILES['archivos']['error'][$i] === 0) {
                        $nombreOriginal = $_FILES['archivos']['name'][$i];
                        $tmpName = $_FILES['archivos']['tmp_name'][$i];
                        $mime = $_FILES['archivos']['type'][$i];

                        $ext = pathinfo($nombreOriginal, PATHINFO_EXTENSION);
                        $nombreLimpio = preg_replace("/[^a-zA-Z0-9.\-_]/", "", $nombreOriginal);
                        $nombreDestino = 'recep_' . $id . '_' . time() . '_' . $i . '.' . $ext;
                        $rutaDestino = $uploadDir . $nombreDestino;

                        if (move_uploaded_file($tmpName, $rutaDestino)) {
                            $stmtArchivo->execute([
                                'id' => $id,
                                'nombre' => $nombreLimpio,
                                'ruta' => $nombreDestino,
                                'mime' => $mime,
                                'user' => $usuarioId
                            ]);
                            $archivosLog .= $nombreLimpio . " ";
                            $cambios = true;
                        }
                    }
                }
            }

            if (!$cambios)
                throw new Exception("Pedido no encontrado o sin cambios.");

            $stmtArea = $this->db->prepare("SELECT area_actual_id FROM pedidos WHERE id = :id");
            $stmtArea->execute(['id' => $id]);
            $areaId = $stmtArea->fetchColumn();

            $obs = 'Datos del pedido actualizados desde Recepción';
            if ($archivosLog !== "")
                $obs .= " | Archivos adjuntados: " . $archivosLog;

            $stmtLog = $this->db->prepare("
                INSERT INTO movimientos_pedido (pedido_id, usuario_id, area_id, accion, observaciones)
                VALUES (:p, :u, :a, 'Editado', :obs)
            ");
            $stmtLog->execute(['p' => $id, 'u' => $usuarioId, 'a' => $areaId, 'obs' => $obs]);

            // LOG AUDITORIA
            $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            $stmtAudit = $this->db->prepare("INSERT INTO auditoria_logs (usuario_id, entidad_tipo, entidad_id, accion, descripcion_accion, ip_address) VALUES (?, 'pedido', ?, 'actualizar', 'Actualizó datos del pedido en Recepción', ?)");
            $stmtAudit->execute([$usuarioId, $id, $ip]);

            $this->jsonResponse(200, "Pedido actualizado correctamente.", null, 'success');

        }
        catch (Exception $e) {
            $this->jsonResponse(400, $e->getMessage());
        }
    }

    /**
     * Elimina (cancela) un pedido marcándolo como cancelado.
     */
    public function delete()
    {
        $this->validarMetodoHttp('POST');
        file_put_contents('/tmp/banner_debug.log', "DELETE PEDIDO: START\n", FILE_APPEND);
        // Solo Admin o SuperAdmin pueden eliminar pedidos
        if (!in_array($_SESSION['role'] ?? '', ['Admin', 'SuperAdmin'])) {
            file_put_contents('/tmp/banner_debug.log', "DELETE PEDIDO: Permission Denied (" . ($_SESSION['role'] ?? 'NULL') . ")\n", FILE_APPEND);
            $this->jsonResponse(403, 'No tienes permiso para eliminar pedidos.');
            return;
        }
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        file_put_contents('/tmp/banner_debug.log', "DELETE PEDIDO: ID=" . ($input['pedido_id'] ?? 'NULL') . "\n", FILE_APPEND);

        try {
            $id = intval($input['pedido_id'] ?? 0);
            if (!$id)
                throw new Exception("ID de pedido inválido.");

            $stmt = $this->db->prepare("UPDATE pedidos SET estado = 'cancelado', deleted_at = NOW() WHERE id = :id");
            $stmt->execute(['id' => $id]);

            if ($stmt->rowCount() === 0)
                throw new Exception("Pedido no encontrado.");

            $usuarioId = $_SESSION['user_id'] ?? null;
            $stmtArea = $this->db->prepare("SELECT area_actual_id FROM pedidos WHERE id = :id");
            $stmtArea->execute(['id' => $id]);
            $areaId = $stmtArea->fetchColumn();
            if ($areaId === false) $areaId = null;

            $stmtLog = $this->db->prepare("
                INSERT INTO movimientos_pedido (pedido_id, usuario_id, area_id, accion, observaciones)
                VALUES (:p, :u, :a, 'Cancelado', 'Pedido eliminado desde Recepción')
            ");
            $stmtLog->execute(['p' => $id, 'u' => $usuarioId, 'a' => $areaId]);

            // LOG AUDITORIA
            $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            $stmtAudit = $this->db->prepare("INSERT INTO auditoria_logs (usuario_id, entidad_tipo, entidad_id, accion, descripcion_accion, ip_address) VALUES (?, 'pedido', ?, 'eliminar', 'Eliminó/canceló pedido en Recepción', ?)");
            $stmtAudit->execute([$usuarioId, $id, $ip]);

            $this->jsonResponse(200, "Pedido eliminado correctamente.", null, 'success');

        }
        catch (Exception $e) {
            file_put_contents('/tmp/banner_debug.log', "DELETE PEDIDO: EXCEPTION=" . $e->getMessage() . "\n", FILE_APPEND);
            $this->jsonResponse(400, $e->getMessage());
        }
    }

    /**
     * Envía un pedido a un área específica (cambio de área + reseteo de fase).
     */
    public function sendToArea()
    {
        $this->validarMetodoHttp('POST');
        // Solo Admin puede enviar pedidos a área desde Recepción
        if (($_SESSION['role'] ?? '') !== 'Admin') {
            $this->jsonResponse(403, 'No tienes permiso para enviar pedidos a un área.');
            return;
        }
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

        try {
            $id = intval($input['pedido_id'] ?? 0);
            $areaDestId = intval($input['area_destino_id'] ?? 0);
            if (!$id)
                throw new Exception("ID de pedido inválido.");
            if (!$areaDestId)
                throw new Exception("Debe seleccionar un área de destino.");

            // Verificar que el área destino existe y está activa
            $stmtChk = $this->db->prepare("SELECT id, nombre FROM areas WHERE id = :id AND estado = 1");
            $stmtChk->execute(['id' => $areaDestId]);
            $areaInfo = $stmtChk->fetch(PDO::FETCH_ASSOC);
            if (!$areaInfo)
                throw new Exception("El área seleccionada no existe o no está activa.");

            // Verificar que el pedido existe usando su id (area_actual_id puede ser null)
            $stmtOri = $this->db->prepare("SELECT id, area_actual_id FROM pedidos WHERE id = :id AND estado NOT IN ('cancelado','completado')");
            $stmtOri->execute(['id' => $id]);
            $pedidoOri = $stmtOri->fetch(PDO::FETCH_ASSOC);
            if (!$pedidoOri)
                throw new Exception("Pedido no encontrado o ya está finalizado.");

            $areaOrigId = $pedidoOri['area_actual_id']; // puede ser null

            $stmt = $this->db->prepare("
                UPDATE pedidos
                SET area_actual_id = :area_dest,
                    fase_actual    = 'recepcion',
                    asignado_a_usuario_id = NULL,
                    last_movement_at = NOW()
                WHERE id = :id
            ");
            $stmt->execute(['area_dest' => $areaDestId, 'id' => $id]);

            $usuarioId = $_SESSION['user_id'] ?? null;

            // Insertar movimiento manejando area_id null
            if ($areaOrigId !== null) {
                $stmtLog = $this->db->prepare("
                    INSERT INTO movimientos_pedido (pedido_id, usuario_id, area_id, accion, observaciones)
                    VALUES (:p, :u, :a, 'Enviado a Área', :obs)
                ");
                $stmtLog->execute([
                    'p' => $id,
                    'u' => $usuarioId,
                    'a' => $areaOrigId,
                    'obs' => "Pedido enviado al área: " . $areaInfo['nombre']
                ]);
            }
            else {
                $stmtLog = $this->db->prepare("
                    INSERT INTO movimientos_pedido (pedido_id, usuario_id, accion, observaciones)
                    VALUES (:p, :u, 'Enviado a Área', :obs)
                ");
                $stmtLog->execute([
                    'p' => $id,
                    'u' => $usuarioId,
                    'obs' => "Pedido enviado desde Recepción al área: " . $areaInfo['nombre']
                ]);
            }

            $this->jsonResponse(200, "Pedido enviado a «{$areaInfo['nombre']}» correctamente.", null, 'success');

        }
        catch (Exception $e) {
            $this->jsonResponse(400, $e->getMessage());
        }
    }

    /**
     * Marca un pedido finalizado como entregado al cliente.
     */
    public function entregar()
    {
        $this->validarMetodoHttp('POST');
        if (($_SESSION['role'] ?? '') !== 'Admin') {
            $this->jsonResponse(403, 'Solo administradores pueden marcar pedidos como entregados.');
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        try {
            $id = intval($input['pedido_id'] ?? 0);
            if (!$id) {
                throw new Exception("ID de pedido inválido.");
            }

            // Verificar pedido
            $stmtChk = $this->db->prepare("SELECT id, estado, entregado FROM pedidos WHERE id = :id");
            $stmtChk->execute(['id' => $id]);
            $pedido = $stmtChk->fetch(PDO::FETCH_ASSOC);

            if (!$pedido)
                throw new Exception("Pedido no encontrado.");
            if ($pedido['estado'] !== 'completado')
                throw new Exception("El pedido debe estar completado para ser entregado.");
            if ($pedido['entregado'] == 1)
                throw new Exception("El pedido ya fue marcado como entregado.");

            // Actualizar entregado a true
            $stmt = $this->db->prepare("UPDATE pedidos SET entregado = 1, updated_at = NOW() WHERE id = :id");
            $stmt->execute(['id' => $id]);

            // Registrar movimiento
            $usuarioId = $_SESSION['user_id'] ?? null;
            $stmtLog = $this->db->prepare("
                INSERT INTO movimientos_pedido (pedido_id, usuario_id, accion, observaciones)
                VALUES (:p, :u, 'Entregado al Cliente', 'El pedido fue entregado al cliente final.')
            ");
            $stmtLog->execute(['p' => $id, 'u' => $usuarioId]);

            $this->jsonResponse(200, "Pedido marcado como entregado.", null, 'success');
        }
        catch (Exception $e) {
            $this->jsonResponse(400, $e->getMessage());
        }
    }

    /**
     * Revierte un pedido de "Entregado" a "No Entregado" (exclusivo).
     */
    public function revertirEntrega()
    {
        $this->validarMetodoHttp('POST');
        if (session_status() === PHP_SESSION_NONE)
            session_start();
        $usuarioId = $_SESSION['user_id'] ?? null;
        $role = $_SESSION['role'] ?? '';

        $stmtPerm = $this->db->prepare("SELECT devolver_pedidos FROM usuarios WHERE id = ?");
        $stmtPerm->execute([$usuarioId]);
        $userPerm = $stmtPerm->fetch(PDO::FETCH_ASSOC);

        if (!in_array($role, ['Admin', 'SuperAdmin']) && empty($userPerm['devolver_pedidos'])) {
            $this->jsonResponse(403, 'No tienes permiso para revertir entregas.');
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        try {
            $id = intval($input['pedido_id'] ?? 0);
            if (!$id) {
                throw new Exception("ID de pedido inválido.");
            }

            // Verificar pedido
            $stmtChk = $this->db->prepare("SELECT id, estado, entregado FROM pedidos WHERE id = :id");
            $stmtChk->execute(['id' => $id]);
            $pedido = $stmtChk->fetch(PDO::FETCH_ASSOC);

            if (!$pedido)
                throw new Exception("Pedido no encontrado.");
            if ($pedido['estado'] !== 'completado')
                throw new Exception("El pedido debe estar completado.");
            if ($pedido['entregado'] != 1)
                throw new Exception("El pedido aún no ha sido entregado, nada que revertir.");

            // Actualizar entregado a false
            $stmt = $this->db->prepare("UPDATE pedidos SET entregado = 0, updated_at = NOW() WHERE id = :id");
            $stmt->execute(['id' => $id]);

            // Registrar movimiento
            $stmtLog = $this->db->prepare("
                INSERT INTO movimientos_pedido (pedido_id, usuario_id, accion, observaciones)
                VALUES (:p, :u, 'Entrega Revertida', 'Se reversó la entrega del pedido volviendo a No Entregado.')
            ");
            $stmtLog->execute(['p' => $id, 'u' => $usuarioId]);

            $this->jsonResponse(200, "Entrega de pedido revertida.", null, 'success');
        }
        catch (Exception $e) {
            $this->jsonResponse(400, $e->getMessage());
        }
    }

    /**
     * Marca un pedido finalizado como pago completo.
     */
    public function marcarPagoCompleto()
    {
        $this->validarMetodoHttp('POST');
        if (($_SESSION['role'] ?? '') !== 'Admin') {
            $this->jsonResponse(403, 'Solo administradores pueden marcar pedidos como pagados.');
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        try {
            $id = intval($input['pedido_id'] ?? 0);
            if (!$id) {
                throw new Exception("ID de pedido inválido.");
            }

            // Verificar pedido
            $stmtChk = $this->db->prepare("SELECT id, estado, estado_pago, total, abonado FROM pedidos WHERE id = :id");
            $stmtChk->execute(['id' => $id]);
            $pedido = $stmtChk->fetch(PDO::FETCH_ASSOC);

            if (!$pedido)
                throw new Exception("Pedido no encontrado.");
            if ($pedido['estado_pago'] === 'pago_completo')
                throw new Exception("El pedido ya está marcado como pago completo.");

            $total = floatval($pedido['total']);

            // Actualizar 
            $stmt = $this->db->prepare("UPDATE pedidos SET estado_pago = 'pago_completo', abonado = :total, updated_at = NOW(), fecha_pago = NOW() WHERE id = :id");
            $stmt->execute(['total' => $total, 'id' => $id]);

            // Registrar movimiento
            $usuarioId = $_SESSION['user_id'] ?? null;
            $stmtLog = $this->db->prepare("
                INSERT INTO movimientos_pedido (pedido_id, usuario_id, accion, observaciones)
                VALUES (:p, :u, 'Pago Completo', 'Se ha marcado manualmente como pago completo.')
            ");
            $stmtLog->execute(['p' => $id, 'u' => $usuarioId]);

            $this->jsonResponse(200, "Pedido marcado como pago completo.", null, 'success');
        }
        catch (Exception $e) {
            $this->jsonResponse(400, $e->getMessage());
        }
    }

    /**
     * Devuelve los pagos realizados de un pedido
     */
    public function getPagos($id)
    {
        $this->validarMetodoHttp('GET');
        
        try {
            $stmt = $this->db->prepare("SELECT total, abonado FROM pedidos WHERE id = ?");
            $stmt->execute([$id]);
            $pedido = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$pedido) throw new Exception("Pedido no encontrado.");

            $stmtPagos = $this->db->prepare("SELECT monto, metodo_pago, DATE_FORMAT(fecha_pago, '%Y-%m-%d %H:%i') as fecha_pago, observacion FROM historial_pagos WHERE pedido_id = ? ORDER BY id DESC");
            $stmtPagos->execute([$id]);
            $pagos = $stmtPagos->fetchAll(PDO::FETCH_ASSOC);

            $this->jsonResponse(200, "Pagos cargados", ['total' => $pedido['total'], 'abonado' => $pedido['abonado'], 'historial' => $pagos], 'success');
        } catch (Exception $e) {
            $this->jsonResponse(400, $e->getMessage());
        }
    }

    /**
     * Agrega un nuevo abono
     */
    public function nuevoAbono()
    {
        $this->validarMetodoHttp('POST');
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        
        try {
            $id = intval($input['pedido_id'] ?? 0);
            $monto = floatval($input['monto'] ?? 0);
            $metodo = in_array($input['metodo_pago'] ?? '', ['efectivo', 'transferencia']) ? $input['metodo_pago'] : 'efectivo';
            $obs = trim($input['observacion'] ?? '');
            $usuarioId = $_SESSION['user_id'] ?? null;

            if (!$id || $monto <= 0) throw new Exception("Datos inválidos.");

            $this->db->beginTransaction();

            $stmtP = $this->db->prepare("SELECT total, abonado, estado_pago FROM pedidos WHERE id = ? FOR UPDATE");
            $stmtP->execute([$id]);
            $pedido = $stmtP->fetch(PDO::FETCH_ASSOC);

            if (!$pedido) throw new Exception("Pedido no encontrado.");
            
            $nuevoAbono = $pedido['abonado'] + $monto;
            $estado = $pedido['estado_pago'];

            if ($nuevoAbono >= $pedido['total']) {
                $estado = 'pago_completo';
                $nuevoAbono = $pedido['total']; // Max el total
                $monto = $nuevoAbono - $pedido['abonado']; // Ajustar monto insertado si se pasa
            } else {
                $estado = 'abono';
            }

            if ($monto <= 0) {
                // Quizá ya estaba pago completo
                throw new Exception("El pedido ya está pagado en su totalidad.");
            }

            // Insertar historial
            $stmtH = $this->db->prepare("INSERT INTO historial_pagos (pedido_id, monto, metodo_pago, usuario_id, observacion) VALUES (?, ?, ?, ?, ?)");
            $stmtH->execute([$id, $monto, $metodo, $usuarioId, $obs]);

            // Actualizar pedido
            $stmtU = $this->db->prepare("UPDATE pedidos SET abonado = ?, estado_pago = ?, fecha_pago = NOW() WHERE id = ?");
            $stmtU->execute([$nuevoAbono, $estado, $id]);

            $this->db->commit();
            $this->jsonResponse(200, "Abono guardado", ['nuevo_abonado' => $nuevoAbono, 'nuevo_estado' => $estado], 'success');
        } catch(Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            $this->jsonResponse(400, $e->getMessage());
        }
    }

    // --- Helpers ---

    private function validarMetodoHttp($metodo = 'POST')
    {
        if ($_SERVER['REQUEST_METHOD'] !== $metodo)
            $this->jsonResponse(405, "Método no permitido.");
    }

    private function jsonResponse($code, $message, $data = null, $status = "error")
    {
        http_response_code($code);
        $response = ["status" => $status, "message" => $message];
        if ($data !== null)
            $response['data'] = $data;
        echo json_encode($response);
        exit;
    }
}