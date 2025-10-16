<?php

/**
 * Database connection handler using Singleton pattern
 */
class Database {
    private static $instance = null;
    private $conn;

    private function __construct() {
        $dsn = sprintf(
            "mysql:host=%s;dbname=%s;port=%s;charset=utf8mb4",
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

    /**
     * Retrieve the singleton Databse instance
     */
    public static function getInstance(): Database {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    /**
     * Get the unserlying PDO connection
     */
    public function getConnection(): PDO {
        return $this->conn;
    }

    /**
     * Prepare and execute a SQL statement safely
     * @param string $sql - The query to execute
     * @param array $params - Optional query parameters for prepared statement
     * @return PDOStatement - The executed PDO statement
     */
    public function query(string $sql, array $params = []): PDOStatement {
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * Fetch a single row from the database
     */
    public function fetch(string $sql, array $params = []): ?array {
        $res = $this->query($sql, $params)->fetch(PDO::FETCH_ASSOC);
        return $res ?: null;
    }

    /**
     * Fetch all rows from the database
     */
    public function fetchAll(string $sql, array $params = []): array {
        return $this->query($sql, $params)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Execute a SQL stqtement without fetching results
     */
    public function execute(string $sql, array $params = []): bool {
        return $this->query($sql, $params) !== false;
    }

    /**
     * Turn off autocommit mode.
     * Changes to the database are not commited until commit() is called.
     */
    public function beginTransaction(): void {
        $this->conn->beginTransaction();
    }

    /**
     * Commit a transaction and return the database connection to autocommit mode
     */
    public function commit(): void {
        $this->conn->commit();
    }

    /**
     * Roll back the current transaction as initiated by beginTransaction()
     */
    public function rollBack(): void {
        $this->conn->rollBack();
    }

    /**
     * Close the database connection and reset the singleton instance
     */
    public function close(): void {
        $this->conn = null;
        self::$instance = null;
    }
}
