<?php

final class PhotoDownloadControllerTest extends DbTestCase {
    private string $mediaDir;

    protected function setUp(): void {
        parent::setUp();
        $this->mediaDir = sys_get_temp_dir() . '/photo-download-test-media-' . uniqid();
        putenv("MEDIA_DIR={$this->mediaDir}");
    }

    protected function tearDown(): void {
        parent::tearDown();

        $db = Database::getInstance();
        $db->execute('SET FOREIGN_KEY_CHECKS=0');
        $db->execute('TRUNCATE TABLE posts');
        $db->execute('SET FOREIGN_KEY_CHECKS=1');

        putenv('MEDIA_DIR');
        foreach (glob("{$this->mediaDir}/*") ?: [] as $file) {
            unlink($file);
        }
        if (is_dir($this->mediaDir)) {
            rmdir($this->mediaDir);
        }
    }

    private function createUser(string $username, string $email, string $password): User {
        $data = new SignupData($username, $email, $password);
        SignupService::getInstance()->processSignup($data);
        $user = User::findByUsername($username);
        $user->email_verified = 1;
        $user->save();

        return $user;
    }

    // downloadPhoto() only exercises its error branches here: the success path
    // ends in readfile() + exit, which would terminate the PHPUnit process.

    public function testDownloadPhotoRejectsInvalidPostId(): void {
        $user = $this->createUser('downloadinvalidid', 'downloadinvalidid@example.com', 'Valid-Password123!');
        $controller = new PhotoDownloadController($user);

        $_SERVER['REQUEST_URI'] = '/photos/download';
        $result = $controller->downloadPhoto();

        $this->assertSame(Response::BAD_REQUEST, $controller->getStatus()['code']);
        $this->assertSame('Invalid post ID', json_decode($result, true)['error']);
    }

    public function testDownloadPhotoRejectsNonexistentPost(): void {
        $user = $this->createUser('downloadnotfound', 'downloadnotfound@example.com', 'Valid-Password123!');
        $controller = new PhotoDownloadController($user);

        $_SERVER['REQUEST_URI'] = '/photos/download?postId=999999';
        $result = $controller->downloadPhoto();

        $this->assertSame(Response::NOT_FOUND, $controller->getStatus()['code']);
        $this->assertSame('Post not found', json_decode($result, true)['error']);
    }

    public function testDownloadPhotoRejectsNotOwnPost(): void {
        $owner = $this->createUser('downloadowner', 'downloadowner@example.com', 'Valid-Password123!');
        $other = $this->createUser('downloadother', 'downloadother@example.com', 'Valid-Password123!');
        $post = new Post('/media/download-not-own.jpg', $owner->id);
        $post->save();

        $controller = new PhotoDownloadController($other);
        $_SERVER['REQUEST_URI'] = '/photos/download?postId=' . $post->id;
        $result = $controller->downloadPhoto();

        $this->assertSame(Response::FORBIDDEN, $controller->getStatus()['code']);
        $this->assertSame('Unauthorized to download this post', json_decode($result, true)['error']);
    }

    public function testDownloadPhotoRejectsMissingFile(): void {
        $user = $this->createUser('downloadmissingfile', 'downloadmissingfile@example.com', 'Valid-Password123!');
        $post = new Post('/media/does-not-exist-on-disk.jpg', $user->id);
        $post->save();

        $controller = new PhotoDownloadController($user);
        $_SERVER['REQUEST_URI'] = '/photos/download?postId=' . $post->id;
        $result = $controller->downloadPhoto();

        $this->assertSame(Response::NOT_FOUND, $controller->getStatus()['code']);
        $this->assertSame('File not found', json_decode($result, true)['error']);
    }
}
