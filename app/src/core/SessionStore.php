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

    /**
     * Establish an authenticated session for the given user.
     * Regenerates the session ID first to prevent session fixation
     * (a pre-login session ID must never remain valid post-login).
     */
    public static function setUserSession(int $userId): void {
        session_regenerate_id(true);
        self::set(SessionKey::UserId, $userId);
    }

    /**
     * Get the current authenticated user's ID from the session.
     */
    public static function activeSession(): bool {
        return (bool) self::get(SessionKey::UserId);
    }

    /**
     * Clear the authenticated session for the current user.
     */
    public static function clearUserSession(): void {
        self::delete(SessionKey::UserId);
    }

    /**
     * Check if the cooldown period for sending emails has passed.
     * Returns the number of seconds remaining until the next email can be sent.
     */
    public static function secondsUntilEmailSendAllowed(): int {
        $lastSentTime = self::get(SessionKey::LastEmailSentTime);
        if ($lastSentTime === null) {
            return 0;
        }

        $elapsedTime = time() - (int)$lastSentTime;
        $cooldownPeriod = 60;

        return max(0, $cooldownPeriod - $elapsedTime);
    }
}
