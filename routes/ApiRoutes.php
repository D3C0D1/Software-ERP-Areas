<?php
namespace Routes;

use App\Controllers\AuthController;
use App\Controllers\KanbanController;
use App\Controllers\DashboardController;
use App\Middlewares\AuthMiddleware;

class ApiRoutes
{

    public function dispatch($method, $uri)
    {
        // Enrutador muy básico pero funcional (Para frameworks sin dependencias)

        // Calcular basePath para redireccionamientos de vistas
        $scriptName = dirname($_SERVER['SCRIPT_NAME']);
        $basePath = str_replace('/public', '', $scriptName);
        if ($basePath === '/' || $basePath === '\\') {
            $basePath = '';
        }

        // Extraemos path limpio de query string para todas las rutas
        $uriParsed = parse_url($uri, PHP_URL_PATH);

        // --- RUTAS FRONTEND (Vistas HTML) ---
        if ($method === 'GET' && ($uriParsed === '/' || $uriParsed === '/login')) {
            require_once dirname(__DIR__) . '/views/login.php';
            exit;
        }

        if ($method === 'GET' && $uriParsed === '/dashboard') {
            require_once dirname(__DIR__) . '/views/dashboard.php';
            exit;
        }

        if ($method === 'GET' && $uriParsed === '/recepcion') {
            require_once dirname(__DIR__) . '/views/recepcion.php';
            exit;
        }

        if ($method === 'GET' && $uriParsed === '/contabilidad') {
            (new AuthMiddleware())->authorizeView(['Admin', 'SuperAdmin'], $basePath);
            require_once dirname(__DIR__) . '/views/contabilidad.php';
            exit;
        }

        if ($method === 'GET' && $uriParsed === '/admin-areas') {
            (new AuthMiddleware())->authorizeView(['Admin', 'SuperAdmin'], $basePath);
            (new \App\Controllers\AreaController())->indexView();
        }

        if ($method === 'GET' && $uriParsed === '/admin-usuarios') {
            (new AuthMiddleware())->authorizeView(['Admin', 'SuperAdmin'], $basePath);
            (new \App\Controllers\UsuarioController())->indexView();
        }

        // Vista Seguimiento Público
        if ($method === 'GET' && strpos($uriParsed, '/seguimiento/') === 0) {
            $token = substr($uriParsed, strlen('/seguimiento/'));
            $_GET['token'] = $token;
            require_once dirname(__DIR__) . '/app/controllers/SeguimientoController.php';
            (new \App\Controllers\SeguimientoController())->registrarAperturaCliente($token);
            require_once dirname(__DIR__) . '/views/seguimiento.php';
            exit;
        }

        // Vista Reporte de Movimientos (solo Admin)
        if ($method === 'GET' && $uriParsed === '/reporte-movimientos') {
            (new AuthMiddleware())->authorizeView(['Admin', 'SuperAdmin'], $basePath);
            require_once dirname(__DIR__) . '/views/reporte_movimientos.php';
            exit;
        }

        // Vista Reportes Pedidos
        if ($method === 'GET' && $uriParsed === '/reportes-pedidos') {
            (new AuthMiddleware())->authorizeView(['Admin', 'Gerente'], $basePath);
            require_once dirname(__DIR__) . '/views/reportes_pedidos.php';
            exit;
        }

        // Vista Mi Cuenta / Perfil de usuario (todos los roles autenticados)
        if ($method === 'GET' && $uriParsed === '/mi-cuenta') {
            (new AuthMiddleware())->handle();
            require_once dirname(__DIR__) . '/views/mi_cuenta.php';
            exit;
        }

        // Vista Kanban por área
        if ($method === 'GET' && $uriParsed === '/kanban') {
            (new AuthMiddleware())->handle();
            require_once dirname(__DIR__) . '/views/kanban.php';
            exit;
        }

        // Vista Configuración (solo Admin)
        if ($method === 'GET' && $uriParsed === '/configuracion') {
            (new AuthMiddleware())->authorizeView(['Admin', 'SuperAdmin'], $basePath);
            require_once dirname(__DIR__) . '/views/configuracion.php';
            exit;
        }

        // Vista Configuración Avanzada (solo Admin) – acepta guión y guión bajo
        if ($method === 'GET' && ($uriParsed === '/configuracion-avanzada' || $uriParsed === '/configuracion_avanzada')) {
            (new AuthMiddleware())->authorizeView(['Admin', 'SuperAdmin'], $basePath);
            require_once dirname(__DIR__) . '/views/configuracion_avanzada.php';
            exit;
        }

        if ($method === 'POST' && $uriParsed === '/debug-payload') {
            error_log("DEBUG-PAYLOAD RECEIVED!");
            file_put_contents(dirname(__DIR__) . '/debug_post.txt', print_r($_POST, true) . "\n\nFILES: " . print_r($_FILES, true) . "\n\nINPUT: " . file_get_contents('php://input'));
            echo json_encode(["status" => "success", "post" => $_POST, "files" => $_FILES, "input" => file_get_contents('php://input')]);
            exit;
        }

        switch ($method) {
            case 'POST':
                // --- AUTH ---
                if ($uri === '/api/login') {
                    $authController = new AuthController();
                    $authController->login();
                }
                else if ($uri === '/api/logout') {
                    $authController = new AuthController();
                    $authController->logout();
                }
                // --- PERFIL DE USUARIO ---
                else if ($uriParsed === '/api/perfil/actualizar') {
                    (new AuthMiddleware())->handle();
                    (new \App\Controllers\PerfilController())->actualizar();
                }

                // --- ADMINISTRACIÓN ---
                else if ($uriParsed === '/api/areas/crear') {
                    (new AuthMiddleware())->authorizeRoles(['Admin', 'SuperAdmin']);
                    (new \App\Middlewares\CsrfMiddleware())->handle();
                    (new \App\Controllers\AreaController())->store();
                }
                else if ($uriParsed === '/api/areas/editar') {
                    (new AuthMiddleware())->authorizeRoles(['Admin', 'SuperAdmin']);
                    (new \App\Middlewares\CsrfMiddleware())->handle();
                    (new \App\Controllers\AreaController())->update();
                }
                else if ($uriParsed === '/api/areas/editar-icono') {
                    (new AuthMiddleware())->authorizeRoles(['Admin', 'SuperAdmin']);
                    (new \App\Middlewares\CsrfMiddleware())->handle();
                    (new \App\Controllers\AreaController())->updateIcono();
                }
                else if ($uriParsed === '/api/areas/eliminar') {
                    (new AuthMiddleware())->authorizeRoles(['Admin', 'SuperAdmin']);
                    (new \App\Middlewares\CsrfMiddleware())->handle();
                    (new \App\Controllers\AreaController())->delete();
                }
                else if ($uriParsed === '/api/usuarios/editar') {
                    (new AuthMiddleware())->authorizeRoles(['Admin', 'SuperAdmin']);
                    (new \App\Middlewares\CsrfMiddleware())->handle();
                    (new \App\Controllers\UsuarioController())->updateAreas();
                }
                else if ($uriParsed === '/api/usuarios/editar-admin') {
                    (new AuthMiddleware())->authorizeRoles(['Admin', 'SuperAdmin']);
                    (new \App\Controllers\UsuarioController())->editarAdmin();
                }
                else if ($uriParsed === '/api/usuarios/crear') {
                    (new AuthMiddleware())->authorizeRoles(['Admin', 'SuperAdmin']);
                    (new \App\Middlewares\CsrfMiddleware())->handle();
                    (new \App\Controllers\UsuarioController())->crear();
                }
                else if ($uriParsed === '/api/usuarios/eliminar') {
                    (new AuthMiddleware())->authorizeRoles(['Admin', 'SuperAdmin']);
                    (new \App\Middlewares\CsrfMiddleware())->handle();
                    (new \App\Controllers\UsuarioController())->eliminar();
                }

                // --- CONFIGURACIÓN DEL SISTEMA ---
                else if ($uriParsed === '/api/config/guardar') {
                    (new AuthMiddleware())->authorizeRoles(['Admin', 'SuperAdmin']);
                    (new \App\Middlewares\CsrfMiddleware())->handle();
                    (new \App\Controllers\ConfiguracionController())->guardar();
                }
                else if ($uriParsed === '/api/config/subir-fondo') {
                    (new AuthMiddleware())->authorizeRoles(['Admin', 'SuperAdmin']);
                    (new \App\Controllers\ConfiguracionController())->subirFondo();
                }
                else if ($uriParsed === '/api/config/limpiar-logs') {
                    (new AuthMiddleware())->authorizeRoles(['Admin', 'SuperAdmin']);
                    (new \App\Middlewares\CsrfMiddleware())->handle();
                    (new \App\Controllers\ConfiguracionController())->limpiarLogs();
                }
                else if ($uriParsed === '/api/config/eliminar-todos-pedidos') {
                    (new AuthMiddleware())->authorizeRoles(['Admin', 'SuperAdmin']);
                    (new \App\Middlewares\CsrfMiddleware())->handle();
                    (new \App\Controllers\ConfiguracionController())->eliminarTodosPedidos();
                }
                else if ($uriParsed === '/api/config/eliminar-auditoria') {
                    (new AuthMiddleware())->authorizeRoles(['Admin', 'SuperAdmin']);
                    (new \App\Middlewares\CsrfMiddleware())->handle();
                    (new \App\Controllers\ConfiguracionController())->eliminarAuditoria();
                }
                else if ($uriParsed === '/api/config/check-autobackup') {
                    (new AuthMiddleware())->handle();
                    (new \App\Controllers\ConfiguracionController())->checkAutoBackup();
                }

                // --- GESTIÓN DE PEDIDOS ---
                else if ($uri === '/api/pedidos/crear') {
                    (new AuthMiddleware())->handle();
                    (new \App\Middlewares\CsrfMiddleware())->handle();
                    (new \App\Controllers\RecepcionController())->store();
                }
                else if ($uri === '/api/pedidos/editar') {
                    (new AuthMiddleware())->handle();
                    (new \App\Middlewares\CsrfMiddleware())->handle();
                    (new \App\Controllers\RecepcionController())->update();
                }
                else if ($uri === '/api/pedidos/eliminar') {
                    (new AuthMiddleware())->authorizeRoles(['Admin', 'SuperAdmin']);
                    (new \App\Middlewares\CsrfMiddleware())->handle();
                    (new \App\Controllers\RecepcionController())->delete();
                }
                else if ($uri === '/api/pedidos/enviar-area') {
                    (new AuthMiddleware())->handle();
                    (new \App\Middlewares\CsrfMiddleware())->handle();
                    (new \App\Controllers\RecepcionController())->sendToArea();
                }
                else if ($uri === '/api/pedidos/entregar') {
                    (new AuthMiddleware())->authorizeRoles(['Admin', 'SuperAdmin']);
                    (new \App\Middlewares\CsrfMiddleware())->handle();
                    (new \App\Controllers\RecepcionController())->entregar();
                }
                else if ($uri === '/api/pedidos/revertir-entrega') {
                    (new AuthMiddleware())->handle();
                    (new \App\Middlewares\CsrfMiddleware())->handle();
                    (new \App\Controllers\RecepcionController())->revertirEntrega();
                }
                else if ($uri === '/api/pedidos/pagado-completo') {
                    (new AuthMiddleware())->authorizeRoles(['Admin', 'SuperAdmin']);
                    (new \App\Middlewares\CsrfMiddleware())->handle();
                    (new \App\Controllers\RecepcionController())->marcarPagoCompleto();
                }
                else if ($uri === '/api/pedidos/nuevo-abono') {
                    (new AuthMiddleware())->handle();
                    (new \App\Middlewares\CsrfMiddleware())->handle();
                    (new \App\Controllers\RecepcionController())->nuevoAbono();
                }

                // --- KANBAN (Protegidos) ---
                else if ($uri === '/api/kanban/tomar') {
                    (new AuthMiddleware())->handle();
                    (new \App\Middlewares\CsrfMiddleware())->handle();
                    (new KanbanController())->tomarPedidoAction();
                }
                else if ($uri === '/api/kanban/finalizar-tarea') {
                    (new AuthMiddleware())->handle();
                    (new KanbanController())->finalizarFaseAction();
                }
                else if ($uri === '/api/kanban/enviar') {
                    (new AuthMiddleware())->handle();
                    (new KanbanController())->despacharSiguienteAreaAction();
                }
                else if ($uri === '/api/kanban/devolver') {
                    (new AuthMiddleware())->handle();
                    (new KanbanController())->devolverPedidoAction();
                }
                else if ($uri === '/api/kanban/mover_libre') {
                    (new AuthMiddleware())->handle();
                    (new \App\Middlewares\CsrfMiddleware())->handle();
                    (new KanbanController())->moverFaseLibreAction();
                }
                else if ($uri === '/api/kanban/finalizar') {
                    (new AuthMiddleware())->handle();
                    (new KanbanController())->completarPedidoAction();
                }

                // --- REPORTES PEDIDOS ---
                else if ($uri === '/api/reportes-pedidos/eliminar-pedidos') {
                    (new AuthMiddleware())->authorizeRoles(['Admin', 'SuperAdmin']);
                    (new \App\Middlewares\CsrfMiddleware())->handle();
                    require_once dirname(__DIR__) . '/app/controllers/ReportesPedidosController.php';
                    (new \App\Controllers\ReportesPedidosController())->eliminarPedidosViejosAction();
                }
                else if ($uri === '/api/reportes-pedidos/eliminar-archivos') {
                    (new AuthMiddleware())->authorizeRoles(['Admin', 'SuperAdmin']);
                    (new \App\Middlewares\CsrfMiddleware())->handle();
                    require_once dirname(__DIR__) . '/app/controllers/ReportesPedidosController.php';
                    (new \App\Controllers\ReportesPedidosController())->purgarArchivosAction();
                }

                // --- SMS MANUAL ---
                else if ($uri === '/api/sms/enviar-manual') {
                    (new AuthMiddleware())->handle();
                    require_once dirname(__DIR__) . '/app/controllers/SmsController.php';
                    (new \App\Controllers\SmsController())->enviarManual();
                }

                else {
                    $this->trigger404($uri);
                }
                break;

            case 'GET':
                // --- CONFIGURACION ---
                if ($uriParsed === '/api/config/probar-sms') {
                    (new AuthMiddleware())->authorizeRoles(['Admin', 'SuperAdmin']);
                    (new \App\Controllers\ConfiguracionController())->probarSms();
                }
                else if ($uriParsed === '/api/config/probar-saldo') {
                    (new AuthMiddleware())->authorizeRoles(['Admin', 'SuperAdmin']);
                    (new \App\Controllers\ConfiguracionController())->probarSaldo();
                }
                else if ($uriParsed === '/api/config/exportar-db') {
                    (new AuthMiddleware())->authorizeRoles(['Admin', 'SuperAdmin']);
                    (new \App\Controllers\ConfiguracionController())->exportarDB();
                }
                // --- DASHBOARD (Solo Gerentes o Admin) ---
                else if ($uri === '/api/dashboard/metricas') {
                    $authMiddleware = new AuthMiddleware();
                    $authMiddleware->authorizeRoles(['Admin', 'Gerente']);
                    (new DashboardController())->getMetricasAction();
                }
                // --- MÉTRICAS POR ÁREA ---
                else if ($uri === '/api/dashboard/areas') {
                    (new AuthMiddleware())->handle();
                    (new DashboardController())->getMetricasAreasAction();
                }
                else if (preg_match('/^\/api\/dashboard\/area\/([0-9]+)\/pedidos$/', $uri, $matches)) {
                    (new AuthMiddleware())->handle();
                    (new DashboardController())->getPedidosAreaAction($matches[1]);
                }
                // --- TABLERO KANBAN POR AREA ---
                // Extrae el ID usando expresiones regulares: /api/kanban/board/3
                else if (preg_match('/^\/api\/kanban\/board\/([0-9]+)$/', $uri, $matches)) {
                    (new AuthMiddleware())->handle();
                    $areaId = $matches[1];
                    (new KanbanController())->getTableroArea($areaId);
                }
                // --- ARCHIVOS ADJUNTOS DE PEDIDO ---
                else if (preg_match('/^\/api\/kanban\/archivos\/([0-9]+)$/', $uri, $matches)) {
                    (new AuthMiddleware())->handle();
                    (new KanbanController())->getArchivosAction($matches[1]);
                }
                // --- GET PAGOS ---
                else if (preg_match('/^\/api\/pedidos\/pagos\/([0-9]+)$/', $uri, $matches)) {
                    (new AuthMiddleware())->handle();
                    (new \App\Controllers\RecepcionController())->getPagos($matches[1]);
                }
                // --- SERVIR ARCHIVOS DE UPLOADS (storage/uploads) ---
                else if (preg_match('/^\/storage\/uploads\/(.+)$/', $uriParsed, $matches)) {
                    (new AuthMiddleware())->handle();
                    $filename = basename($matches[1]);
                    $filePath = dirname(__DIR__) . '/storage/uploads/' . $filename;
                    if (!file_exists($filePath)) {
                        http_response_code(404);
                        echo json_encode(["status" => "error", "message" => "Archivo no encontrado."]);
                        exit;
                    }
                    // Detectar MIME type
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mime = finfo_file($finfo, $filePath);
                    finfo_close($finfo);
                    if (!$mime)
                        $mime = 'application/octet-stream';
                    // Limpiar cualquier cabecera JSON previa
                    header_remove('Content-Type');
                    header('Content-Type: ' . $mime);
                    header('Content-Length: ' . filesize($filePath));
                    header('Content-Disposition: inline; filename="' . $filename . '"');
                    header('Cache-Control: private, max-age=86400');
                    readfile($filePath);
                    exit;
                }
                // --- SEGUIMIENTO PUBLICO (POLLING) ---
                else if (strpos($uri, '/api/seguimiento/') === 0) {
                    $token = substr($uri, strlen('/api/seguimiento/'));
                    (new \App\Controllers\SeguimientoController())->getProgresoAction($token);
                }
                else if ($uri === '/api/reportes/movimientos') {
                    (new AuthMiddleware())->authorizeRoles(['Admin', 'SuperAdmin']);
                    require_once dirname(__DIR__) . '/app/controllers/ReportesController.php';
                    (new \App\Controllers\ReportesController())->getMovimientosAction();
                }

                // --- REPORTES PEDIDOS ---
                else if ($uriParsed === '/api/reportes-pedidos/list') {
                    (new AuthMiddleware())->authorizeRoles(['Admin', 'Gerente']);
                    require_once dirname(__DIR__) . '/app/controllers/ReportesPedidosController.php';
                    (new \App\Controllers\ReportesPedidosController())->getListAction();
                }
                else if (preg_match('/^\/api\/reportes-pedidos\/detalles\/([0-9]+)$/', $uri, $matches)) {
                    (new AuthMiddleware())->authorizeRoles(['Admin', 'Gerente']);
                    require_once dirname(__DIR__) . '/app/controllers/ReportesPedidosController.php';
                    (new \App\Controllers\ReportesPedidosController())->getDetallesSeguimientoAction($matches[1]);
                }

                else {
                    $this->trigger404($uri);
                }
                break;

            default:
                $this->trigger404($uri);
                break;
        }
    }

    private function trigger404($uri = '')
    {
        http_response_code(404);
        echo json_encode(["status" => "error", "message" => "Endpoint no encontrado o método inválido. RAW URI: " . $uri]);
        exit;
    }
}