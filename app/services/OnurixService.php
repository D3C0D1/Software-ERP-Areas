<?php
namespace App\Services;

use PDO;
use Config\Database;
use Exception;

/**
 * Servicio Onurix — SMS y WhatsApp usando curl nativo
 * SMS:       https://www.onurix.com/api/v1/sms/send
 * WhatsApp:  https://www.onurix.com/api/v1/whatsapp/send
 */
class OnurixService
{
    private $db;
    private string $smsUrl = 'https://www.onurix.com/api/v1/sms/send';
    private string $whatsappUrl = 'https://www.onurix.com/api/v1/whatsapp/send';
    private string $client = '';
    private string $key = '';
    private string $empresa = 'Banner';
    private string $wPhoneSenderId = '';
    private string $wTemplateId = ''; // plantilla recepción
    private string $wTemplateIdFinalizar = ''; // plantilla finalizar/recoger
    private string $wVarNombre = 'nombre';
    private string $wVarLink = 'link';

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->cargarCredenciales();
    }

    private function cargarCredenciales(): void
    {
        try {
            $stmt = $this->db->query(
                "SELECT clave, valor FROM configuracion WHERE clave IN
                 ('onurix_api_id','onurix_api_key','empresa_nombre',
                  'whatsapp_phone_sender_id','whatsapp_template_id',
                  'whatsapp_template_id_finalizar',
                  'whatsapp_var_nombre','whatsapp_var_link')"
            );
            $rows = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
            $this->client = $rows['onurix_api_id'] ?? '';
            $this->key = $rows['onurix_api_key'] ?? '';
            $this->empresa = $rows['empresa_nombre'] ?? 'Banner';
            $this->wPhoneSenderId = $rows['whatsapp_phone_sender_id'] ?? '';
            $this->wTemplateId = $rows['whatsapp_template_id'] ?? '';
            $this->wTemplateIdFinalizar = !empty($rows['whatsapp_template_id_finalizar'])
                ? $rows['whatsapp_template_id_finalizar']
                : $this->wTemplateId; // fallback a plantilla principal
            $this->wVarNombre = !empty($rows['whatsapp_var_nombre']) ? $rows['whatsapp_var_nombre'] : 'nombre';
            $this->wVarLink = !empty($rows['whatsapp_var_link']) ? $rows['whatsapp_var_link'] : 'link';
        }
        catch (\Exception $e) {
        // Si la tabla no existe aún, dejamos vacío
        }
    }

    // -------------------------------------------------------------------
    //  SMS
    // -------------------------------------------------------------------

    /**
     * Envía SMS directamente a la API de Onurix.
     * Retorna ['ok' => bool, 'response' => array].
     */
    public function enviarSMS(string $celular, string $texto): array
    {
        if (empty($this->client) || empty($this->key)) {
            return ['ok' => false, 'response' => ['msg' => 'Credenciales Onurix no configuradas.']];
        }
        if (empty($celular)) {
            return ['ok' => false, 'response' => ['msg' => 'Número de teléfono vacío.']];
        }

        $celularLimpio = preg_replace('/[^0-9]/', '', $celular);
        if (strlen($celularLimpio) === 10) {
            $celularLimpio = '57' . $celularLimpio;
        }

        $ch = curl_init($this->smsUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/x-www-form-urlencoded',
                'Accept: application/json',
            ],
            CURLOPT_POSTFIELDS => http_build_query([
                'client' => $this->client,
                'key' => $this->key,
                'phone' => $celularLimpio,
                'sms' => $texto,
                'groups' => '',
            ]),
        ]);

        $raw = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr = curl_error($ch);
        curl_close($ch);

        if ($curlErr) {
            return ['ok' => false, 'response' => ['msg' => 'cURL error: ' . $curlErr]];
        }

        $decoded = json_decode($raw, true) ?? ['raw' => $raw];
        $ok = ($httpCode >= 200 && $httpCode < 300) && !isset($decoded['error']);

        return ['ok' => $ok, 'http_code' => $httpCode, 'response' => $decoded];
    }

    /** Compatibilidad con código anterior */
    public function encolarSMS(string $celular, string $texto): bool
    {
        return $this->enviarSMS($celular, $texto)['ok'];
    }

    // -------------------------------------------------------------------
    //  WHATSAPP
    // -------------------------------------------------------------------

    /**
     * Envía un mensaje de WhatsApp mediante plantilla de Onurix.
     *
     * Payload real de Onurix (claves nombradas según la plantilla META):
     *   phones   => "573001234567"           (URL-param: phone-sender-id, template-id, client, key)
     *   header   => { "1": { "type": "image", "value": {...} } }  (opcional)
     *   body     => { "nombre": { "type": "text", "value": "..." },
     *                 "link":   { "type": "text", "value": "..." } }
     *   buttons  => {}   (plural — vacío si no aplica)
     *   footer   => {}   (vacío si no aplica)
     *
     * @param string $celular    Número del destinatario.
     * @param string $nombreCliente Nombre del cliente (variable «nombre» de la plantilla).
     * @param string $linkSeguimiento URL de seguimiento (variable «link» de la plantilla).
     */
    public function enviarWhatsApp(
        string $celular,
        string $nombreCliente = '',
        string $linkSeguimiento = '',
        string $templateIdOverride = '' // '' = usar plantilla de recepción por defecto
        ): array
    {
        // Seleccionar template
        $templateId = !empty($templateIdOverride) ? $templateIdOverride : $this->wTemplateId;

        if (empty($this->client) || empty($this->key)) {
            return ['ok' => false, 'response' => ['msg' => 'Credenciales Onurix no configuradas.']];
        }
        if (empty($this->wPhoneSenderId)) {
            return ['ok' => false, 'response' => ['msg' => 'ID de número remitente WhatsApp no configurado.']];
        }
        if (empty($templateId)) {
            return ['ok' => false, 'response' => ['msg' => 'ID de plantilla WhatsApp no configurado.']];
        }
        if (empty($celular)) {
            return ['ok' => false, 'response' => ['msg' => 'Número de teléfono vacío.']];
        }

        // Formatear número (código de país sin '+')
        $celularLimpio = preg_replace('/[^0-9]/', '', $celular);
        if (strlen($celularLimpio) === 10) {
            $celularLimpio = '57' . $celularLimpio;
        }

        // --- CONSTRUCCIÓN DEL PAYLOAD SEGÚN EL TIPO DE PLANTILLA ---
        $header = (object)[];
        $body = (object)[];

        // 1. Plantilla FINALIZAR (recojer/finalizado) -> body: { guia }
        if (!empty($templateIdOverride) && $templateIdOverride === $this->wTemplateIdFinalizar) {
            // En vez de enviar solo "#PED-0001", enviamos el enlace de seguimiento completo ($linkSeguimiento)
            $body = ["guia" => ["type" => "text", "value" => $linkSeguimiento]];
        }
        // 2. Plantilla CREAR (recepcion) -> header: { nombre }, body: { link }
        else {
            if ($nombreCliente !== '') {
                // Según la plantilla nueva, el nombre va en el HEADER
                $header = ["nombre" => ["type" => "text", "value" => $nombreCliente]];
            }
            if ($linkSeguimiento !== '') {
                // Según la plantilla nueva, el link va en el BODY
                $body = ["link" => ["type" => "text", "value" => $linkSeguimiento]];
            }
        }

        // Payload final — solo "phones" (no groups)
        $payload = [
            'phones' => $celularLimpio,
            'header' => empty((array)$header) ? (object)[] : $header,
            'body' => empty((array)$body) ? (object)[] : $body,
            'buttons' => (object)[],
            'footer' => (object)[],
        ];

        $url = $this->whatsappUrl
            . '?key=' . urlencode($this->key)
            . '&client=' . urlencode($this->client)
            . '&templateId=' . urlencode($templateId)
            . '&phoneSenderId=' . urlencode($this->wPhoneSenderId);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Accept: application/json',
            ],
            CURLOPT_POSTFIELDS => json_encode($payload),
        ]);

        $raw = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr = curl_error($ch);
        curl_close($ch);

        if ($curlErr) {
            return ['ok' => false, 'response' => ['msg' => 'cURL error: ' . $curlErr]];
        }

        $decoded = json_decode($raw, true) ?? ['raw' => $raw];
        $ok = ($httpCode >= 200 && $httpCode < 300) && !isset($decoded['error']);

        return ['ok' => $ok, 'http_code' => $httpCode, 'response' => $decoded];
    }

    /**
     * Atajo para enviar WhatsApp usando la plantilla de FINALIZAR.
     */
    public function enviarWhatsAppFinalizar(
        string $celular,
        string $nombreCliente = '',
        string $linkSeguimiento = ''
        ): array
    {
        return $this->enviarWhatsApp(
            $celular,
            $nombreCliente,
            $linkSeguimiento,
            $this->wTemplateIdFinalizar
        );
    }

    // -------------------------------------------------------------------
    //  PRUEBA
    // -------------------------------------------------------------------


    /** Prueba rápida de credenciales para el panel de Configuración. */
    public function probarConexion(): array
    {
        if (empty($this->client) || empty($this->key)) {
            return ['ok' => false, 'msg' => 'Configura primero el Client ID y la API Key.'];
        }
        $result = $this->enviarSMS('57300000000', 'Test desde Banner ERP - Ignorar.');
        if ($result['ok'] || (isset($result['response']['error']) && $result['response']['error'] != 1003)) {
            return ['ok' => true, 'msg' => 'Conexión exitosa con Onurix.', 'detail' => $result['response']];
        }
        return ['ok' => false, 'msg' => 'Credenciales inválidas o error de API.', 'detail' => $result['response']];
    }

    /**
     * Obtiene el saldo actual de Onurix.
     */
    public function obtenerSaldo(): array
    {
        if (empty($this->client) || empty($this->key)) {
            return ['ok' => false, 'msg' => 'Credenciales Onurix no configuradas.'];
        }

        $url = 'https://www.onurix.com/api/v1/balance?client=' . urlencode($this->client) . '&key=' . urlencode($this->key);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_HTTPGET => true,
        ]);

        $raw = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr = curl_error($ch);
        curl_close($ch);

        if ($curlErr) {
            return ['ok' => false, 'msg' => 'cURL error: ' . $curlErr];
        }

        $decoded = json_decode($raw, true) ?? [];
        if (isset($decoded['balance'])) {
            return ['ok' => true, 'balance' => $decoded['balance']];
        }

        return ['ok' => false, 'msg' => 'No se pudo obtener el saldo.', 'detail' => $decoded];
    }
}