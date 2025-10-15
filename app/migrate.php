<?php

require_once __DIR__.'/src/Core/Database.php';

$db = Database::getInstance();
$pdo = $db->getConnection();

$models = [];
foreach (glob(__DIR__.'/src/Models/*.php') as $modelFile) {
    require_once $modelFile;
    $models[] = basename($modelFile, ".php");
}

$foreignKeyStatements = [];

foreach ($models as $modelClass) {
    $model = new $modelClass();

    $sql = $model->createMigrationSql();
    try {
        $db->execute($sql);
    } catch (PDOException $e) {
        error_log("Migration of '$modelClass' failed: $e->getMessage()");
        exit (1);
    }

    $foreignKeys = $model->createForeignKeysSql();
    if (!empty($foreignKeys)) {
        $foreignKeyStatements = array_merge($foreignKeyStatements, $foreignKeys);
    }
}

foreach ($foreignKeyStatements as $sql) {
    try {
        $db->execute($sql);
    } catch (PDOException $e) {
        error_log("FK creation failed: $e->getMessage()");
        exit (1);
    }
}
