<?php

require_once __DIR__.'/Database.php';

abstract class Model {
    protected static ?Database $db = null;
    protected static string $name;
    protected static array $schema = [];

    public function createMigrationSql(): string {
        $tableName = static::$name;
        $columns = [];
        foreach(static::$schema as $field => $type) {
            $columns[] = "`$field` $type";
        }
        return ("CREATE TABLE IF NOT EXISTS `$tableName` (" . implode(", ", $columns) . ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    }

    protected static function getDb(): Database {
        if (self::$db === null) {
            self::$db = Database::getInstance();
        }
        return self::$db;
    }
}
