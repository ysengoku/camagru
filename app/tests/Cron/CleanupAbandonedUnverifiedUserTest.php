<?php

final class CleanupAbandonedUnverifiedUserTest extends DbTestCase {
    private static function seedUnverifiedUser(string $username, bool $abandoned = false): void {
        $email    = $username . '@example.com';
        $password = 'Valid-Password123!';
        $data = new SignupData($username, $email, $password);
        SignupService::getInstance()->processSignup($data);

        if ($abandoned) {
            $backdatedCreatedAt = (new DateTime('-25 hours'))->format('Y-m-d H:i:s');
            $sql = 'UPDATE users SET created_at = :created_at WHERE username = :username';
            $params = ['created_at' => $backdatedCreatedAt, 'username' => $username];
            Database::getInstance()->execute($sql, $params);
        }
    }

    public function testDeleteAbandonedUnverifiedUser(): void {
        $username = 'abandoned';
        self::seedUnverifiedUser($username, true);

        User::deleteAbandonedUnverified(24);

        $user = User::findByUsername($username);
        $this->assertNull($user);
    }

    public function testKeepRecentUnverifiedUser(): void {
        $username = 'recent';
        self::seedUnverifiedUser($username, false);

        User::deleteAbandonedUnverified(24);

        $user = User::findByUsername($username);
        $this->assertNotNull($user);
    }

    public function testCleanupScriptDeleteAbandonedUnverifiedUser(): void {
        $usernameAbandoned = 'abandoned';
        self::seedUnverifiedUser($usernameAbandoned, true);

        $usernamePending = 'pending';
        self::seedUnverifiedUser($usernamePending, false);

        ob_start();
        require __DIR__ . '/../../cron/cleanup_unverified_users.php';
        $output = ob_get_clean();

        $this->assertStringContainsString('Cleanup of abandoned unverified accounts completed.', $output);
        $this->assertNull(User::findByUsername($usernameAbandoned));
        $this->assertNotNull(User::findByUsername($usernamePending));
    }
}
