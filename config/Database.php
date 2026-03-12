<?php
namespace Config;

use PDO;
use PDOException;

class Database
{
    private static $instance = null;
    private $pdo;

    private $host;
    private $user;
    private $pass;
    private $dbname;

    private function __construct()
    {
        // Detectar entorno: si el host es o contiene banner.com.co, usamos Hostinger, de lo contrario local.
        $httpHost = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';

        if (strpos($httpHost, 'banner.com.co') !== false) {
            // Entorno Hostinger (Producción)
            $this->host = 'localhost';
            $this->user = 'u106336323_banner';
            $this->pass = 'A0347a1312#';
            $this->dbname = 'u106336323_banner';
        }
        else {
            // Entorno Local (Desarrollo)
            $this->host = 'localhost';
            $this->user = 'root';
            $this->pass = 'mysql';
            $this->dbname = 'erp_mvc';
        }

        $dsn = "mysql:host={$this->host};dbname={$this->dbname};charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        try {
            $this->pdo = new PDO($dsn, $this->user, $this->pass, $options);
            // Sincronizar zona horaria con Colombia (UTC-5)
            $this->pdo->exec("SET time_zone = '-05:00'");
        }
        catch (PDOException $e) {
            // En producción, se debe loguear el error y no mostrar el mensaje
            throw new \Exception("Error conectando a la base de datos: " . $e->getMessage());
        }
    }

    // Singleton pattern
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance->pdo;
    }

    // Evitar clonar y des-serializar
    private function __clone()
    {
    }
    public function __wakeup()
    {
    }
}