<?php

require_once __DIR__ . '/../../src/Views/post/comments.php';

final class SecurityTest extends DbTestCase {
    // ===== CSRF ================================================================

    public function testCsrfMismatchRejectsUnsafeRequest(): void {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REQUEST_URI'] = '/api/login';
        $_SERVER['HTTP_X_CSRF_TOKEN'] = 'wrong-token';
        $_POST = [];

        $app = new Application();
        ob_start();
        $app->run();
        ob_get_clean();

        $this->assertSame(Response::FORBIDDEN, http_response_code());
    }

    public function testCsrfMatchAllowsRequestThrough(): void {
        $validToken = Request::getCsrfToken();

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REQUEST_URI'] = '/api/login';
        $_SERVER['HTTP_X_CSRF_TOKEN'] = $validToken;
        $_POST = ['username' => '', 'password' => ''];

        $app = new Application();
        ob_start();
        $app->run();
        ob_get_clean();

        $this->assertNotSame(Response::FORBIDDEN, http_response_code());
    }

    public function testCsrfExemptMethodBypassesCheck(): void {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/api/validation-rules';
        unset($_SERVER['HTTP_X_CSRF_TOKEN']);

        $app = new Application();
        ob_start();
        $app->run();
        ob_get_clean();

        $this->assertNotSame(Response::FORBIDDEN, http_response_code());
    }

    // ===== XSS ==================================================================

    public function testCommentContentIsEscapedInRenderedHtml(): void {
        $comment = new PostCommentData(
            id: 1,
            author_id: 1,
            author_name: 'Attacker',
            author_avatar: null,
            created_at: (new DateTime())->format('Y-m-d H:i:s'),
            content: '<script>alert(1)</script>',
        );

        $html = render_comment($comment, null);

        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
    }

    // ===== Path traversal ======================================================

    public function testDownloadPhotoCannotEscapeMediaDirViaPathTraversal(): void {
        $mediaDir = sys_get_temp_dir() . '/security-test-media-' . uniqid();
        mkdir($mediaDir);
        $secretDir = sys_get_temp_dir() . '/security-test-secret-' . uniqid();
        mkdir($secretDir);
        file_put_contents($secretDir . '/secret.txt', 'top secret');
        putenv("MEDIA_DIR={$mediaDir}");

        $data = new SignupData('traversaluser', 'traversaluser@example.com', 'Valid-Password123!');
        SignupService::getInstance()->processSignup($data);
        $user = User::findByUsername('traversaluser');
        $user->email_verified = 1;
        $user->save();

        $traversalPath = '/media/../' . basename($secretDir) . '/secret.txt';
        $post = new Post($traversalPath, $user->id);
        $post->save();

        $controller = new PhotoDownloadController($user);
        $_SERVER['REQUEST_URI'] = '/photos/download?postId=' . $post->id;
        $result = $controller->downloadPhoto();

        $this->assertSame(Response::NOT_FOUND, $controller->getStatus()['code']);
        $this->assertSame('File not found', json_decode($result, true)['error']);

        unlink($secretDir . '/secret.txt');
        rmdir($secretDir);
        rmdir($mediaDir);
        putenv('MEDIA_DIR');

        $db = Database::getInstance();
        $db->execute('SET FOREIGN_KEY_CHECKS=0');
        $db->execute('TRUNCATE TABLE posts');
        $db->execute('SET FOREIGN_KEY_CHECKS=1');
    }
}
