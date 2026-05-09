<?php

abstract class Model {
    protected static ?Database $db = null;
    protected static string $name;
    protected static array $schema    = [];
    protected static array $relations = [];

    protected static function getDb(): Database {
        if (self::$db === null) {
            self::$db = Database::getInstance();
        }
        return self::$db;
    }

    protected static function findById(string $id) {
        $db = self::getDb();
        $table = static::$name;
        $sql = "SELECT * FROM `$table` WHERE id = :id";
        $stmt = $db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($data) {
            $instance = new static();
            foreach ($data as $key => $value) {
                if (property_exists($instance, $key)) {
                    $instance->$key = $value;
                }
            }
            return $instance;
        }

        return null;
    }

    protected static function findAll(): array {
        $db = self::getDb();
        $table = static::$name;
        $sql = "SELECT * FROM `$table`";
        $stmt = $db->query($sql);
        $results = [];
        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $instance = new static();
            foreach ($data as $key => $value) {
                if (property_exists($instance, $key)) {
                    $instance->$key = $value;
                }
            }
            $results[] = $instance;
        }
        return $results;
    }

    protected function save(): bool {
        $db = self::getDb();
        $table = static::$name;
        $fields = array_keys(static::$schema);
        $placeholders = array_map(fn($field) => ":$field", $fields);
        $sql = "INSERT INTO `$table` (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")";
        $stmt = $db->prepare($sql);
        $params = [];
        foreach ($fields as $field) {
            $params[$field] = $this->$field;
        }
        return $stmt->execute($params);
    }

    protected function delete(): bool {
        $db = self::getDb();
        $table = static::$name;
        $sql = "DELETE FROM `$table` WHERE id = :id";
        $stmt = $db->prepare($sql);
        return $stmt->execute(['id' => $this->id]);
    }
}
