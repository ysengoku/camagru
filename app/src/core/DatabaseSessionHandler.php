<?php

/** 
 * Custom session handler,
 * implementation from SessionHandlerInterface (PHP standard interface)
 * that stores session data in the database instead of files.
 */

final class DatabaseSessionHandler implements SessionHandlerInterface {
    private Database $db;

    // Execute when session_start() is called
    public function open(string $savePath, string $sessionName): bool {
        $this->db = Database::getInstance();
        return true;
    }

    public function close(): bool {
        return true;
    }

    // Execute when session_start() is called
    public function read(string $session_id): string {
        $sql = "
            SELECT data
            FROM sessions
            WHERE session_token = ?
            AND expired_at > NOW()
            LIMIT 1
        ";
      
        $row = $this->db->fetch($sql, [$session_id]);

        return $row ? $row['data'] : '';
    }

    // Execute when $_SESSION is modified (at script end)
    public function write(string $session_id, string $data): bool {
        $user_id = SessionStore::get(SessionKey::UserId) ?? null;
        $csrf_token = SessionStore::get(SessionKey::CsrfToken) ?? '';
        $expired_at = date('Y-m-d H:i:s', time() + ini_get('session.gc_maxlifetime'));

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
      
        return $this->db->execute($sql, [$session_id, $user_id, $csrf_token, $data, $expired_at]);
    }

    // Execute when session_destroy() is called (logout)
    public function destroy(string $session_id): bool {
        $sql = "
            DELETE FROM sessions
            WHERE session_token = ?
        ";

        return $this->db->execute($sql, [$session_id]);
    }

    // Execute when garbage collection is triggered
    public function gc(int $maxLifetime): int|false {
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
