<?php
namespace App\Controllers;

use Config\Database;
use App\Middlewares\AuthMiddleware;

class ClienteController
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function indexView()
    {
        // Solo Admin y SuperAdmin
        $auth = new AuthMiddleware();
        $auth->authorizeView(['Admin', 'SuperAdmin']);
        
        $basePath = str_replace('/public', '', dirname($_SERVER['SCRIPT_NAME']));
        if ($basePath === '/' || $basePath === '\\') {
            $basePath = '';
        }
        
        require_once dirname(__DIR__, 2) . '/views/clientes.php';
        exit;
    }

    public function getList()
    {
        // Solo Admin y SuperAdmin
        $auth = new AuthMiddleware();
        $auth->authorizeRoles(['Admin', 'SuperAdmin']);

        try {
            $stmt = $this->db->query("
                SELECT cliente_nombre AS nombre, 
                       cliente_telefono AS telefono, 
                       COUNT(id) AS compras,
                       MAX(created_at) AS ultima_compra,
                       SUM(total) as monto_total
                FROM pedidos
                WHERE cliente_nombre IS NOT NULL AND cliente_nombre != '' AND deleted_at IS NULL
                GROUP BY cliente_nombre, cliente_telefono
                ORDER BY cliente_nombre ASC
            ");
            $clientes = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            echo json_encode(["status" => "success", "data" => $clientes]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Error al obtener clientes: " . $e->getMessage()]);
        }
        exit;
    }

    public function merge()
    {
        // Solo Admin y SuperAdmin
        $auth = new AuthMiddleware();
        $auth->authorizeRoles(['Admin', 'SuperAdmin']);

        $telefono = $_POST['telefono'] ?? '';
        $nombreFinal = $_POST['nombre_final'] ?? '';

        if (empty($telefono) || empty($nombreFinal)) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Faltan datos obligatorios (teléfono o nombre final)."]);
            exit;
        }

        try {
            // Actualizar todos los pedidos con este teléfono para que tengan el mismo nombre
            $stmt = $this->db->prepare("UPDATE pedidos SET cliente_nombre = :nombre WHERE cliente_telefono = :telefono");
            $stmt->execute([
                ':nombre' => $nombreFinal,
                ':telefono' => $telefono
            ]);

            echo json_encode(["status" => "success", "message" => "Clientes fusionados exitosamente."]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Error al fusionar: " . $e->getMessage()]);
        }
        exit;
    }

    public function mergeAll()
    {
        $auth = new AuthMiddleware();
        $auth->authorizeRoles(['Admin', 'SuperAdmin']);

        $input = json_decode(file_get_contents('php://input'), true);
        $merges = $input['merges'] ?? [];

        if (empty($merges) || !is_array($merges)) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "No hay datos de fusión."]);
            exit;
        }

        try {
            $this->db->beginTransaction();
            $stmt = $this->db->prepare("UPDATE pedidos SET cliente_nombre = :nombre WHERE cliente_telefono = :telefono");
            foreach ($merges as $m) {
                if (!empty($m['telefono']) && !empty($m['nombre_final'])) {
                    $stmt->execute([
                        ':nombre' => $m['nombre_final'],
                        ':telefono' => $m['telefono']
                    ]);
                }
            }
            $this->db->commit();
            echo json_encode(["status" => "success", "message" => "Todos los clientes duplicados fueron fusionados."]);
        } catch (\Exception $e) {
            $this->db->rollBack();
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Error al fusionar: " . $e->getMessage()]);
        }
        exit;
    }

    /**
     * Editar nombre y teléfono de un cliente (actualiza todos los pedidos que coincidan)
     */
    public function updateClient()
    {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $oldName = trim($input['old_nombre'] ?? '');
            $oldPhone = trim($input['old_telefono'] ?? '');
            $newName = trim($input['new_nombre'] ?? '');
            $newPhone = trim($input['new_telefono'] ?? '');

            if (empty($oldName) || empty($newName)) {
                throw new \Exception("El nombre del cliente es obligatorio.");
            }

            $this->db->beginTransaction();

            $stmt = $this->db->prepare("
                UPDATE pedidos 
                SET cliente_nombre = :newName, cliente_telefono = :newPhone 
                WHERE cliente_nombre = :oldName AND (cliente_telefono = :oldPhone OR (:oldPhone2 = '' AND (cliente_telefono IS NULL OR cliente_telefono = '')))
                AND deleted_at IS NULL
            ");
            $stmt->execute([
                ':newName' => $newName,
                ':newPhone' => $newPhone,
                ':oldName' => $oldName,
                ':oldPhone' => $oldPhone,
                ':oldPhone2' => $oldPhone
            ]);

            $affected = $stmt->rowCount();
            $this->db->commit();

            echo json_encode([
                "status" => "success",
                "message" => "Cliente actualizado. $affected pedido(s) actualizados."
            ]);
        } catch (\Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Error al actualizar: " . $e->getMessage()]);
        }
        exit;
    }
    /**
     * Obtiene todos los pedidos de un cliente específico
     */
    public function getOrdersByClient()
    {
        try {
            $phone = $_GET['telefono'] ?? '';
            $name = $_GET['nombre'] ?? '';

            if (empty($phone) && empty($name)) {
                throw new \Exception("Se requiere teléfono o nombre para buscar.");
            }

            $query = "
                SELECT id, total, abonado, estado, created_at, descripcion
                FROM pedidos
                WHERE deleted_at IS NULL
            ";
            $params = [];

            if (!empty($phone)) {
                $query .= " AND cliente_telefono = :phone";
                $params['phone'] = $phone;
            } else {
                $query .= " AND cliente_nombre = :name";
                $params['name'] = $name;
            }

            $query .= " ORDER BY created_at DESC LIMIT 100";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            $orders = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            echo json_encode(["status" => "success", "data" => $orders]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => $e->getMessage()]);
        }
        exit;
    }
}
