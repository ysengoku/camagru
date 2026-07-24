<?php

/** 
 * Custom session handler,
 * implementation from SessionHandlerInterface (PHP standard interface)
 * that stores session data in the database instead of files.
 */

final class DatabaseSessionHandler implements SessionHandlerInterface {
    /** @psalm-suppress PropertyNotSetInConstructor - always set in open() before any other method is called */
    private Database $db;

    // Execute when session_start() is called
    /** @psalm-suppress PossiblyUnusedMethod - invoked internally by PHP's session engine */
    #[Override]
    public function open(string $path, string $name): bool {
        $this->db = Database::getInstance();
        return true;
    }

    /** @psalm-suppress PossiblyUnusedMethod - invoked internally by PHP's session engine */
    #[Override]
    public function close(): bool {
        return true;
    }

    // Execute when session_start() is called
    /** @psalm-suppress PossiblyUnusedMethod - invoked internally by PHP's session engine */
    #[Override]
    public function read(string $id): string {
        $sql = "
            SELECT data
            FROM sessions
            WHERE session_token = ?
            AND expired_at > NOW()
            LIMIT 1
        ";
      
        $row = $this->db->fetch($sql, [$id]);

        return $row !== null && is_string($row['data']) ? $row['data'] : '';
    }

    // Execute when $_SESSION is modified (at script end)
    /** @psalm-suppress PossiblyUnusedMethod - invoked internally by PHP's session engine */
    #[Override]
    public function write(string $id, string $data): bool {
        $user_id = SessionStore::get(SessionKey::UserId) ?? null;
        $csrf_token = SessionStore::get(SessionKey::CsrfToken) ?? '';
        $maxLifetime = ini_get('session.gc_maxlifetime');
        $maxLifetime = is_numeric($maxLifetime) ? (int) $maxLifetime : 1440;
        $expired_at = date('Y-m-d H:i:s', time() + $maxLifetime);

        $sql = "
            INSERT INTO sessions
                (session_token, user_id, csrf_token, data, expired_at)
            VALUES (?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                user_id = VALUES(user_id),
                csrf_token = VALUES(csrf_token),
                data = VALUES(data),
                expired_at = VALUES(expired_at)
        ";
      
        return $this->db->execute($sql, [$id, $user_id, $csrf_token, $data, $expired_at]);
    }

    // Execute when session_destroy() is called (logout)
    /** @psalm-suppress PossiblyUnusedMethod - invoked internally by PHP's session engine */
    #[Override]
    public function destroy(string $id): bool {
        $sql = "
            DELETE FROM sessions
            WHERE session_token = ?
        ";

        return $this->db->execute($sql, [$id]);
    }

    // Execute when garbage collection is triggered
    /** @psalm-suppress PossiblyUnusedMethod - invoked internally by PHP's session engine */
    #[Override]
    public function gc(int $max_lifetime): int|false {
        $sql = "
            DELETE FROM sessions
            WHERE expired_at < NOW()
        ";

        try {
            $stmt = $this->db->query($sql);

            return $stmt->rowCount();
        } catch (PDOException $e) {
            error_log("Failed to execute garbage collection: " . $e->getMessage());

            return false;
        }
    }
}
