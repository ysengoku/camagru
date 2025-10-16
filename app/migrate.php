<?php

require_once __DIR__.'/src/Core/Database.php';

$db = Database::getInstance();
$dbh = $db->getConnection();

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
        $dbh->exec($sql);
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
        $dbh->exec($sql);
    } catch (PDOException $e) {
        error_log("Foreign Keys creation failed: $e->getMessage()");
        exit (1);
    }
}
