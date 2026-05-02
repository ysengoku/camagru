<?php

class Database
{
    private static $instance = null;
    private $conn;

    private function __construct()
    {
        $dsn = sprintf(
            "mysql:host=%s;dbname=%s;port=%s",
            getenv('DB_HOST'),
            getenv('MYSQL_DATABASE'),
            getenv('DB_PORT')
        );
        $options = [
            PDO::ATTR_PERSISTENT => true,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ];

        try {
            $this->conn = new PDO($dsn, getenv('MYSQL_USER'), getenv('MYSQL_PASSWORD'), $options);
        } catch (PDOException $e) {
            error_log("Connection failed: " . $e->getMessage());
            exit(1);
        }
    }

    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection(): PDO
    {
        return $this->conn;
    }

    public function query(string $sql, array $params = []): PDOStatement
    {
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public function fetch(string $sql, array $params = []): ?array
    {
        $res = $this->query($sql, $params)->fetch(PDO::FETCH_ASSOC);
        return $res ?: null;
    }

    public function fetchAll(string $sql, array $params = []): array
    {
        return $this->query($sql, $params)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function execute(string $sql, array $params = []): bool
    {
        return $this->query($sql, $params) !== false;
    }

    public function close(): void
    {
        $this->conn = null;
        self::$instance = null;
    }
}
