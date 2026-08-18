<?php

final class ProfileControllerTest extends DbTestCase {
    protected function tearDown(): void {
        parent::tearDown();

        $db = Database::getInstance();
        $db->execute('SET FOREIGN_KEY_CHECKS=0');
        $db->execute('TRUNCATE TABLE posts');
        $db->execute('SET FOREIGN_KEY_CHECKS=1');
    }

    private function createUser(string $username, string $email, string $password): User {
        $data = new SignupData($username, $email, $password);
        SignupService::getInstance()->processSignup($data);
        $user = User::findByUsername($username);
        $user->email_verified = 1;
        $user->save();

        return $user;
    }

    // ===== POST /api/profile ===================================================

    public function testUpdateSucceeds(): void {
        $user = $this->createUser('profilesuccess', 'profilesuccess@example.com', 'Valid-Password123!');
        $controller = new ProfileController($user);

        $_POST = [
            'username' => 'profilenewname',
            'email' => $user->email,
            'notifications' => '1',
        ];
        $result = $controller->update();
        $data = json_decode($result, true);

        $this->assertSame(Response::OK, $controller->getStatus()['code']);
        $this->assertArrayHasKey('message', $data);
        $this->assertArrayHasKey('emailVerificationRequired', $data);
        $this->assertArrayHasKey('avatarHtml', $data);
    }

    public function testUpdateRejectsInvalidUsername(): void {
        $user = $this->createUser('profileinvalid', 'profileinvalid@example.com', 'Valid-Password123!');
        $controller = new ProfileController($user);

        $_POST = [
            'username' => 'bad name!!',
            'email' => $user->email,
        ];
        $result = $controller->update();
        $data = json_decode($result, true);

        $this->assertSame(Response::BAD_REQUEST, $controller->getStatus()['code']);
        $this->assertArrayHasKey('error', $data);
    }

    // ===== GET /api/avatar-options =============================================

    public function testGetAvatarOptionsSucceeds(): void {
        $user = $this->createUser('avataroptions', 'avataroptions@example.com', 'Valid-Password123!');
        $post = new Post('/media/avatar-options-route.jpg', $user->id);
        $post->save();

        $controller = new ProfileController($user);
        $_SERVER['REQUEST_URI'] = '/api/avatar-options?page=1';
        $result = $controller->getAvatarOptions();
        $data = json_decode($result, true);

        $this->assertSame(Response::OK, $controller->getStatus()['code']);
        $this->assertArrayHasKey('html', $data);
        $this->assertSame(1, $data['page']);
        $this->assertArrayHasKey('totalPages', $data);
    }
}
