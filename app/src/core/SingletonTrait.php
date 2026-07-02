<?php

trait SingletonTrait {
    private static ?self $instance = null;

    public static function getInstance(): self {
        if (self::$instance === null) {
            self::$instance = new static();
        }
        return self::$instance;
    }
}
