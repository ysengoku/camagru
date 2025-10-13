<?php

class Database {
    private $host;
    private $name;
    private $port;
    private $user;
    private $password;

    private $conn;

    public function __construct() {
        $this->host = getenv('DB_HOST');
        $this->name = getenv('DB_NAME');
        $this->port = getenv('DB_PORT');
        $this->user = getenv('MYSQL_USER');
        $this->password = getenv('MYSQL_PASSWORD');

        $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->name . ";port=" . $this->port;
        $options = [
            PDO::ATTR_PERSISTENT => true,
            PDO::ERRMODE => PDO::ERRMODE_EXCEPTION
        ];

        try {
            $this->conn = new PDO($dsn, $this->user, $this->password, $options);
        } catch (PDOException $e) {
            echo "Connection failed: " . $e->getMessage();
        }
    }
}

?>
