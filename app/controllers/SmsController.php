<?php
namespace App\Controllers;

use Exception;
use App\Services\OnurixService;

class SmsController
{
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function enviarManual()
    {
        $this->validarMetodoHttp('POST');
        $input = json_decode(file_get_contents('php://input'), true) ?? [];

        try {
            $numero = trim($input['numero'] ?? '');
            $texto = trim($input['texto'] ?? '');

            if (empty($numero) || strlen($numero) < 7) {
                throw new Exception("El número de teléfono es inválido.");
            }
            if (empty($texto)) {
                throw new Exception("El mensaje no puede estar vacío.");
            }

            require_once __DIR__ . '/../services/OnurixService.php';
            $onurix = new OnurixService();

            // Envío del SMS
            $resultado = $onurix->enviarSMS($numero, $texto);

            if (isset($resultado['ok']) && $resultado['ok']) {
                $this->jsonResponse(200, "SMS enviado exitosamente.", null, "success");
            }
            else {
                $errMsg = $resultado['response']['msg'] ?? 'Error desconocido en Onurix.';
                throw new Exception("Error al procesar el envío del SMS con Onurix: " . $errMsg);
            }

        }
        catch (Exception $e) {
            $this->jsonResponse(400, $e->getMessage());
        }
    }

    private function validarMetodoHttp($metodo = 'POST')
    {
        if ($_SERVER['REQUEST_METHOD'] !== $metodo) {
            $this->jsonResponse(405, "Método HTTP no permitido.");
        }
    }

    private function jsonResponse($code, $message, $data = null, $status = "error")
    {
        http_response_code($code);
        $response = ["status" => $status, "message" => $message];
        if ($data !== null) {
            $response['data'] = $data;
        }
        echo json_encode($response);
        exit;
    }
}