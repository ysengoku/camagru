<?php

abstract class Model {
    protected static ?Database $db = null;
    protected static string $name;
    protected static array $schema    = [];
    /** @var list<array{0: string, 1: string, 2: string}> */
    protected static array $relations = [];
    /** @var list<string> */
    protected array $errors = [];

    protected static function getDb(): Database {
        if (self::$db === null) {
            self::$db = Database::getInstance();
        }
        return self::$db;
    }

    /**
     * Map array data to instance properties based on schema.
     * * @param array<string, mixed> $data
     * @return static
     */
    protected static function fromRow(array $data): self {
        $instance = new static();
        foreach ($data as $key => $value) {
            if (property_exists($instance, $key)) {
                $instance->$key = $value;
            }
        }
        return $instance;
    }

    /**
     * Find an record by its ID.
     * @param string $id
     * @return static|null
     */
    protected static function findById(string $id) {
        $db = self::getDb();
        $table = static::$name;
        $sql = "SELECT * FROM `$table` WHERE id = :id LIMIT 1";

        $data = $db->fetch($sql, ['id' => $id]);

        return $data ? self::fromRow($data) : null;
    }

    /**
     * Find all records in the table.
     * @return list<static>
     */
    protected static function findAll(): array {
        $db = self::getDb();
        $table = static::$name;
        $sql = "SELECT * FROM `$table`";
        
        $rows = $db->fetchAll($sql);

        return array_map([static::class, 'fromRow'], $rows);
    }

    /**
     * Save the current instance to the database. (insert or update)
     * @return bool
     */
    public function save(): bool {
        if (!$this->validate()) {
            return false;
        }
        if (!$this->beforeSave()) {
            return false;
        }

        return $this->id > 0 ? $this->update() : $this->insert();
    }

    /**
     * Update the current instance in the database.
     * @return bool
     */
    protected function update(): bool {
        $db = self::getDb();
        $table = static::$name;

        $fields = $this->getPersistableFields();
        $sets = array_map(fn($field) => "`$field` = :$field", $fields);
        $sql = "UPDATE `$table` SET " . implode(', ', $sets) . " WHERE id = :id";

        $params = ['id' => $this->id];
        foreach ($fields as $field) {
            $params[$field] = $this->$field;
        }

        try {
            $db->query($sql, $params);

            return true;
        } catch (PDOException $e) {
            $this->errors[] = 'Database error: Failed to update record.';
            error_log("Update failed: " . $e->getMessage());

            return false;
        }
    }

    /**
     * Insert the current instance into the database.
     * @return bool
     */
    protected function insert(): bool {
        $db = self::getDb();
        $table = static::$name;

        $fields = $this->getPersistableFields();
        $placeholders = array_map(fn($field) => ":$field", $fields);
        $sql = "INSERT INTO `$table` (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")";


        $params = [];
        foreach ($fields as $field) {
            $params[$field] = $this->$field;
        }

        try {
            $db->query($sql, $params);

            if (property_exists($this, 'id')) {
                $this->id = (int) $db->getConnection()->lastInsertId();
            }
            $this->refresh();

            return true;
        } catch (PDOException $e) {
            $this->errors[] = 'Database error: Failed to insert record.';
            error_log("Insert failed: " . $e->getMessage());

            return false;
        }
    }

    /**
     * Get fields that should be persisted to the database (excludes auto-generated fields).
     *
     * @return list<string>
     */
    private function getPersistableFields(): array {
        return array_filter(
            array_keys(static::$schema),
            fn($field) => $field !== 'id' && $field !== 'created_at'
        );
    }

    /**
     * Delete the current instance from the database.
     * @return bool
     */
    public function delete(): bool {
        if ($this->id <= 0) {
            return false;
        }

        $db = self::getDb();
        $table = static::$name;
        $sql = "DELETE FROM `$table` WHERE id = :id";

        return $db->execute($sql, ['id' => $this->id]);
    }

    /**
     * Refresh the current instance's data from the database.
     * @return void
     */
    public function refresh(): void {
        if ($this->id <= 0) {
            return; 
        }

        $freshInstance = self::findById((string) $this->id);
        if ($freshInstance === null) {
            return;
        }

        foreach (array_keys(static::$schema) as $field) {
            $this->$field = $freshInstance->$field;
        }
    }

    /**
     * Validate the current instance's data before saving.
     * To be overridden in subclasses for specific validation logic.
     * @return bool
     */
    public function validate(): bool {
        return true;
    }

    /**
     * Hook method called before saving the instance.
     * Can be overridden in subclasses for pre-save logic.
     * @return bool
     */
    protected function beforeSave(): bool {
        return true;
    }

    /**
     * Get the list of validation errors.
     * @return list<string>
     */
    public function getErrors(): array {
        return $this->errors;
    }
}

