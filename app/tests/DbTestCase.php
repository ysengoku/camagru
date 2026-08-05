<?php

use PHPUnit\Framework\TestCase;

abstract class DbTestCase extends TestCase {
    protected function tearDown(): void {
        $db = Database::getInstance();
        $db->execute('SET FOREIGN_KEY_CHECKS=0');
        $db->execute('TRUNCATE TABLE users');
        $db->execute('SET FOREIGN_KEY_CHECKS=1');
    }
}
