<?php

final class PostControllerTest extends DbTestCase {
    protected function tearDown(): void {
        parent::tearDown();

        $db = Database::getInstance();
        $db->execute('SET FOREIGN_KEY_CHECKS=0');
        $db->execute('TRUNCATE TABLE posts');
        $db->execute('TRUNCATE TABLE comments');
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

    public function testViewThrowsForNonNumericPostId(): void {
        $controller = new PostController();
        $_SERVER['REQUEST_URI'] = '/post?postId=not-a-number';

        $this->expectException(HTTPNotFoundException::class);
        $controller->view();
    }

    public function testViewThrowsForNonexistentPost(): void {
        $controller = new PostController();
        $_SERVER['REQUEST_URI'] = '/post?postId=999999';

        $this->expectException(HTTPNotFoundException::class);
        $controller->view();
    }

    public function testViewReturnsFullPageWhenNotXhr(): void {
        $owner = $this->createUser('postviewowner', 'postviewowner@example.com', 'Valid-Password123!');
        $post = new Post('/media/post-view-full.jpg', $owner->id);
        $post->save();

        unset($_SERVER['HTTP_X_REQUESTED_WITH']);
        $_SERVER['REQUEST_URI'] = '/post?postId=' . $post->id;
        $controller = new PostController();
        $html = $controller->view();

        $this->assertStringContainsString('<!doctype html>', $html);
    }

    public function testViewReturnsFragmentWhenXhr(): void {
        $owner = $this->createUser('postviewxhrowner', 'postviewxhrowner@example.com', 'Valid-Password123!');
        $post = new Post('/media/post-view-fragment.jpg', $owner->id);
        $post->save();

        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        $_SERVER['REQUEST_URI'] = '/post?postId=' . $post->id;
        $controller = new PostController();
        $html = $controller->view();

        $this->assertStringNotContainsString('<!doctype html>', $html);
        unset($_SERVER['HTTP_X_REQUESTED_WITH']);
    }
}
