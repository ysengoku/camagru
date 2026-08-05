<?php

final class AuthInputValidator {
    private static ?array $rules = null;

    private static function rules(): array {
        return self::$rules ??= require __DIR__ . '/../../config/validation.php';
    }

    public static function validateUsername(string $username): ?string {
        $rules = self::rules();
        if (empty($username)) {
            return $rules['username']['messages']['required'];
        }
        if (strlen($username) < $rules['username']['minLength'] || strlen($username) > $rules['username']['maxLength']) {
            return $rules['username']['messages']['length'];
        }
        if (!preg_match('/' . $rules['username']['pattern'] . '/', $username)) {
            return $rules['username']['messages']['pattern'];
        }
        return null;
    }

    public static function validateEmail(string $email): ?string {
        $rules = self::rules();
        if (empty($email)) {
            return $rules['email']['messages']['required'];
        }
        if (!preg_match('/' . $rules['email']['pattern'] . '/', $email)) {
            return $rules['email']['messages']['pattern'];
        }
        if (strlen($email) > $rules['email']['maxLength']) {
            return $rules['email']['messages']['length'];
        }
        return null;
    }

    public static function validatePassword(string $password): ?string {
        $rules = self::rules();
        if (empty($password)) {
            return $rules['password']['messages']['required'];
        }
        if (strlen($password) < $rules['password']['minLength'] || strlen($password) > $rules['password']['maxLength']) {
            return $rules['password']['messages']['length'];
        }
        if ($rules['password']['requireLower'] && !preg_match('/[a-z]/', $password)
            || ($rules['password']['requireUpper'] && !preg_match('/[A-Z]/', $password))
            || ($rules['password']['requireDigit'] && !preg_match('/\d/', $password))
            || !preg_match('/' . $rules['password']['specialCharPattern'] . '/', $password)) {
            return $rules['password']['messages']['pattern'];
        }
        return null;
    }
}

