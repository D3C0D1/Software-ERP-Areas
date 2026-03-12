<?php
namespace App\Controllers;

use App\Services\PipelineService;
use Exception;

class KanbanController
{
    private $pipelineService;

    public function __construct()
    {
        $this->pipelineService = new PipelineService();
        if (session_status() === PHP_SESSION_NONE)
            session_start();
    }

    private function validateUserArea($areaId)
    {
        $userId = $_SESSION['user_id'] ?? null;
        if (in_array($_SESSION['role'] ?? '', ['Admin', 'SuperAdmin']))
            return true;

        $db = \Config\Database::getInstance();
        $stmt = $db->prepare("SELECT 1 FROM usuario_areas WHERE usuario_id = :u AND area_id = :a");
        $stmt->execute(['u' => $userId, 'a' => $areaId]);

        if (!$stmt->fetch()) {
            $this->jsonResponse(403, "No tienes permisos para acceder a esta área productiva.");
        }
    }

    private function validatePedidoAuth($pedidoId)
    {
        $db = \Config\Database::getInstance();
        $stmt = $db->prepare("SELECT area_actual_id FROM pedidos WHERE id = :id");
        $stmt->execute(['id' => $pedidoId]);
        $area = $stmt->fetchColumn();
        if (!$area)
            $this->jsonResponse(404, "Pedido no encontrado.");
        $this->validateUserArea($area);
    }

    public function getTableroArea($areaId)
    {
        try {
            $pedidos = $this->pipelineService->obtenerTableroKaban($areaId);
            $isAdmin = ($_SESSION['role'] ?? '') === 'Admin';

            // Reestructurar para el Frontend (Agrupar por fase)
            $tablero = [
                'recepcion' => [],
                'proceso' => [],
                'preparado' => []
            ];

            foreach ($pedidos as $p) {
                // Ocultar precios a roles no-Admin
                if (!$isAdmin) {
                    unset($p['total'], $p['abonado']);
                }
                $tablero[$p['fase_actual']][] = $p;
            }

            $this->jsonResponse(200, "Tablero cargado", $tablero, "success");
        }
        catch (Exception $e) {
            $this->jsonResponse(500, $e->getMessage());
        }
    }

    public function tomarPedidoAction()
    {
        try {
            $this->validarMetodoHttp('POST');
            $input = $this->getInput();
            if (empty($input['pedido_id']))
                throw new Exception("Falta ID de pedido.");

            $this->pipelineService->iniciarProceso($input['pedido_id'], $_SESSION['user_id'] ?? null);
            $this->jsonResponse(200, "Has tomado el pedido para proceso.", null, "success");

        }
        catch (Exception $e) {
            $this->jsonResponse(400, $e->getMessage());
        }
    }

    public function finalizarFaseAction()
    {
        try {
            $this->validarMetodoHttp('POST');
            $input = $this->getInput();
            if (empty($input['pedido_id']))
                throw new Exception("Falta ID de pedido.");

            $this->pipelineService->marcarPreparado($input['pedido_id'], $_SESSION['user_id'] ?? null);
            $this->jsonResponse(200, "Producto preparado y listo para enviar al siguiente área.", null, "success");

        }
        catch (Exception $e) {
            $this->jsonResponse(400, $e->getMessage());
        }
    }

    /**
     * Marca un pedido como completado (estado final).
     */
    public function completarPedidoAction()
    {
        try {
            $this->validarMetodoHttp('POST');
            $input = $this->getInput();
            if (empty($input['pedido_id']))
                throw new Exception("Falta ID de pedido.");

            $db = \Config\Database::getInstance();
            $pedidoId = intval($input['pedido_id']);

            // Verificar que existe
            $stmt = $db->prepare("SELECT area_actual_id, cliente_nombre, cliente_telefono, token_seguimiento FROM pedidos WHERE id = :id");
            $stmt->execute(['id' => $pedidoId]);
            $pedidoData = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (!$pedidoData)
                throw new Exception("Pedido no encontrado.");

            $areaId = $pedidoData['area_actual_id'];

            // Marcar como completado
            $upd = $db->prepare("UPDATE pedidos SET estado = 'completado', fase_actual = 'preparado', last_movement_at = NOW() WHERE id = :id");
            $upd->execute(['id' => $pedidoId]);

            // Log
            $log = $db->prepare("INSERT INTO movimientos_pedido (pedido_id, usuario_id, area_id, accion, observaciones) VALUES (:p, :u, :a, 'Completado', 'Pedido finalizado y completado')");
            $log->execute(['p' => $pedidoId, 'u' => $_SESSION['user_id'] ?? null, 'a' => $areaId]);

            // Intentar enviar SMS de finalizado si hay teléfono y está marcado
            $enviarSmsInput = !empty($input['send_sms']);
            if ($enviarSmsInput && !empty($pedidoData['cliente_telefono'])) {
                $stmtCfg = $db->query("SELECT clave, valor FROM configuracion WHERE clave IN ('sms_finalizar', 'empresa_nombre')");
                $cfg = $stmtCfg->fetchAll(\PDO::FETCH_KEY_PAIR);
                $plantilla = $cfg['sms_finalizar'] ?? 'Hola {nombre}, su pedido ha sido terminado, ya lo puede recoger en {empresa}';
                $empresa = $cfg['empresa_nombre'] ?? 'Banner';

                // Enlace de seguimiento real
                $basePath = preg_replace('/\/public\/index\.php$/i', '', $_SERVER['SCRIPT_NAME']);
                $enlace = $_SERVER['HTTP_HOST'] . $basePath . "/seguimiento.php?token=" . ($pedidoData['token_seguimiento'] ?? $pedidoId);
                $numeroPedido = 'PED-' . str_pad($pedidoId, 4, '0', STR_PAD_LEFT);
                $mensaje = str_replace(
                ['{nombre}', '{numero_pedido}', '{link_seguimiento}', '{empresa}'],
                [$pedidoData['cliente_nombre'], $numeroPedido, $enlace, $empresa],
                    $plantilla
                );

                require_once __DIR__ . '/../services/OnurixService.php';
                $onurix = new \App\Services\OnurixService();
                $onurix->enviarSMS($pedidoData['cliente_telefono'], $mensaje);
            }

            // Enviar WhatsApp por plantilla al finalizar si está marcado y activo globalmente
            $enviarWa = !empty($input['send_whatsapp']);
            if ($enviarWa && !empty($pedidoData['cliente_telefono'])) {
                $stmtWaCfg = $db->query("SELECT clave, valor FROM configuracion WHERE clave IN ('whatsapp_activo')");
                $waCfg = $stmtWaCfg->fetchAll(\PDO::FETCH_KEY_PAIR);
                $waActivo = ($waCfg['whatsapp_activo'] ?? '1') === '1';

                if ($waActivo) {
                    $basePath2 = preg_replace('/\/public\/index\.php$/i', '', $_SERVER['SCRIPT_NAME']);
                    $enlaceWa = $_SERVER['HTTP_HOST'] . $basePath2 . '/seguimiento.php?token=' . ($pedidoData['token_seguimiento'] ?? $pedidoId);

                    if (!isset($onurix)) {
                        require_once __DIR__ . '/../services/OnurixService.php';
                        $onurix = new \App\Services\OnurixService();
                    }

                    // Usa la plantilla "recoger_pedido" (whatsapp_template_id_finalizar)
                    $onurix->enviarWhatsAppFinalizar(
                        $pedidoData['cliente_telefono'],
                        $pedidoData['cliente_nombre'],
                        $enlaceWa
                    );
                }
            }

            $this->jsonResponse(200, "Pedido finalizado correctamente.", null, "success");
        }
        catch (Exception $e) {
            $this->jsonResponse(400, $e->getMessage());
        }
    }

    /**
     * Devuelve la lista de archivos adjuntos de un pedido.
     */
    public function getArchivosAction($pedidoId)
    {
        try {
            $db = \Config\Database::getInstance();
            $pedidoId = intval($pedidoId);
            $stmt = $db->prepare("
                SELECT id, nombre_archivo, ruta_almacenamiento, tipo_mime, created_at
                FROM archivos
                WHERE entidad_tipo = 'pedido'
                  AND entidad_id = :id
                  AND deleted_at IS NULL
                ORDER BY created_at DESC
            ");
            $stmt->execute(['id' => $pedidoId]);
            $archivos = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            $this->jsonResponse(200, "OK", $archivos, "success");
        }
        catch (Exception $e) {
            $this->jsonResponse(500, $e->getMessage());
        }
    }

    public function despacharSiguienteAreaAction()
    {
        try {
            $this->validarMetodoHttp('POST');
            $input = $this->getInput();
            if (empty($input['pedido_id']) || empty($input['area_destino_id']))
                throw new Exception("Faltan parámetros de envío.");

            $this->pipelineService->enviarAreaDestino($input['pedido_id'], $_SESSION['user_id'] ?? null, $input['area_destino_id']);
            $this->jsonResponse(200, "Pedido enviado a la siguiente estación.", null, "success");

        }
        catch (Exception $e) {
            $this->jsonResponse(400, $e->getMessage());
        }
    }

    public function devolverPedidoAction()
    {
        try {
            $this->validarMetodoHttp('POST');
            $input = $this->getInput();
            if (empty($input['pedido_id']) || empty($input['area_destino_id']) || empty($input['motivo'])) {
                throw new Exception("Faltan parámetros de devolución o el motivo está vacío.");
            }

            $this->pipelineService->devolverPedido($input['pedido_id'], $_SESSION['user_id'] ?? null, $input['area_destino_id'], $input['motivo']);
            $this->jsonResponse(200, "Pedido devuelto registrada exitosamente.", null, "success");

        }
        catch (Exception $e) {
            $this->jsonResponse(400, $e->getMessage());
        }
    }

    public function moverFaseLibreAction()
    {
        try {
            $this->validarMetodoHttp('POST');
            $input = $this->getInput();
            if (empty($input['pedido_id']) || empty($input['nueva_fase'])) {
                throw new Exception("Faltan parámetros: pedido_id o nueva_fase.");
            }

            // Validar que nueva_fase es válida
            if (!in_array($input['nueva_fase'], ['recepcion', 'proceso', 'preparado'])) {
                throw new Exception("Fase inválida.");
            }

            $this->pipelineService->moverFaseLibre($input['pedido_id'], $_SESSION['user_id'] ?? null, $input['nueva_fase']);
            $this->jsonResponse(200, "Pedido movido exitosamente a " . $input['nueva_fase'], null, "success");

        }
        catch (Exception $e) {
            $this->jsonResponse(400, $e->getMessage());
        }
    }

    // --- Helpers Utilitarios --- 

    private function getInput()
    {
        return json_decode(file_get_contents('php://input'), true) ?? $_POST;
    }

    private function validarMetodoHttp($metodo = 'POST')
    {
        if ($_SERVER['REQUEST_METHOD'] !== $metodo) {
            $this->jsonResponse(405, "Método no permitido.");
        }
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