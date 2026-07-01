<?php

final class SessionStore {
    public static function set(SessionKey $key, string|int $value): void {
        $_SESSION[$key->value] = $value;
    }

    public static function get(SessionKey $key): string|int|null {
        return $_SESSION[$key->value] ?? null;
    }

    public static function delete(SessionKey $key): void {
        unset($_SESSION[$key->value]);
    }
}
