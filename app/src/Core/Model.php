<?php

abstract class Model
{
    protected static ?Database $db        = null;
    protected static string $name;
    protected static array $schema    = [];
    protected static array $relations = [];

    public function createMigrationSql(): string
    {
        $tableName = static::$name;
        $columns = [];
        foreach (static::$schema as $field => $type) {
            $columns[] = "`$field` $type";
        }
        return ("CREATE TABLE IF NOT EXISTS `$tableName` (" . implode(", ", $columns) . ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    }

    public function createForeignKeySql(): string
    {
        $tableName = static::$name;
        $queries = [];
        foreach (static::$relations as $field => $relation) {
            list($refTable, $refField, $onDelete) = $relation;
            $queries[] = "ALTER TABLE `$tableName` "
                . "ADD CONSTRAINT `fk_{$tableName}_{$field}` "
                . "FOREIGN KEY (`$field`) "
                . "REFERENCES `$refTable`(`$refField`) "
                . "ON DELETE $onDelete;";
        }
        return $queries;
    }

    protected static function getDb(): Database
    {
        if (self::$db === null) {
            self::$db = Database::getInstance();
        }
        return self::$db;
    }
}
