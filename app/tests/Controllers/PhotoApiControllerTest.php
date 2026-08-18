<?php

final class PhotoApiControllerTest extends DbTestCase {
    private static string $validImagePath;
    private static string $stickerPath;
    private static string $stickerFullPath;
    private string $mediaDir;

    public static function setUpBeforeClass(): void {
        $canvas = imagecreatetruecolor(10, 10);
        imagefilledrectangle($canvas, 0, 0, 9, 9, imagecolorallocate($canvas, 255, 0, 0));
        self::$validImagePath = sys_get_temp_dir() . '/photo-api-test-image-' . uniqid() . '.jpg';
        imagejpeg($canvas, self::$validImagePath);

        self::$stickerPath = '/assets/stickers/photo-api-test-sticker.png';
        self::$stickerFullPath = Path::join(realpath(Path::getPublicPath()), self::$stickerPath);
        if (!is_dir(dirname(self::$stickerFullPath))) {
            mkdir(dirname(self::$stickerFullPath), 0777, true);
        }
        $sticker = imagecreatetruecolor(5, 5);
        imagepng($sticker, self::$stickerFullPath);
    }

    public static function tearDownAfterClass(): void {
        @unlink(self::$stickerFullPath);
        @unlink(self::$validImagePath);
    }

    protected function setUp(): void {
        parent::setUp();
        $this->mediaDir = sys_get_temp_dir() . '/photo-api-test-media-' . uniqid();
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

    private function validUploadedFile(): array {
        return [
            'name' => 'photo.jpg',
            'type' => 'image/jpeg',
            'tmp_name' => self::$validImagePath,
            'error' => UPLOAD_ERR_OK,
            'size' => filesize(self::$validImagePath),
        ];
    }

    private function validStickerPayload(): array {
        return [['path' => self::$stickerPath, 'width' => 5, 'height' => 5, 'x' => 0, 'y' => 0]];
    }

    // ===== create() =========================================================

    public function testCreateRejectsMissingBaseImage(): void {
        $user = $this->createUser('missingimg', 'missingimg@example.com', 'Valid-Password123!');
        $controller = new PhotoApiController($user);

        $_FILES = [];
        $_POST = ['data' => json_encode(['stickers' => $this->validStickerPayload()])];
        $result = $controller->create();

        $this->assertSame(Response::UNPROCESSABLE, $controller->getStatus()['code']);
        $this->assertSame('Missing required elements', json_decode($result, true)['error']);
    }

    public function testCreateRejectsMissingStickers(): void {
        $user = $this->createUser('nostickers', 'nostickers@example.com', 'Valid-Password123!');
        $controller = new PhotoApiController($user);

        $_FILES = ['image' => $this->validUploadedFile()];
        $_POST = ['data' => json_encode(['stickers' => []])];
        $result = $controller->create();

        $this->assertSame(Response::UNPROCESSABLE, $controller->getStatus()['code']);
        $this->assertSame('Missing required elements', json_decode($result, true)['error']);
    }

    public function testCreateSucceedsReturns201WithHtml(): void {
        $user = $this->createUser('creatorsuccess', 'creatorsuccess@example.com', 'Valid-Password123!');
        $controller = new PhotoApiController($user);

        $_FILES = ['image' => $this->validUploadedFile()];
        $_POST = ['data' => json_encode(['stickers' => $this->validStickerPayload()])];
        $result = $controller->create();
        $data = json_decode($result, true);

        $this->assertSame(Response::CREATED, $controller->getStatus()['code']);
        $this->assertSame('Post created successfully', $data['message']);
        $this->assertNotSame('', $data['html']);
        $this->assertSame(1, Post::countByUserId($user->id));
    }

    // ===== delete() =========================================================

    public function testDeleteRejectsInvalidPostId(): void {
        $user = $this->createUser('invalidid', 'invalidid@example.com', 'Valid-Password123!');
        $controller = new PhotoApiController($user);

        $_SERVER['REQUEST_URI'] = '/api/photos';
        $result = $controller->delete();

        $this->assertSame(Response::BAD_REQUEST, $controller->getStatus()['code']);
        $this->assertSame('Invalid post ID', json_decode($result, true)['error']);
    }

    public function testDeleteRejectsNonexistentPost(): void {
        $user = $this->createUser('deleternotfound', 'deleternotfound@example.com', 'Valid-Password123!');
        $controller = new PhotoApiController($user);

        $_SERVER['REQUEST_URI'] = '/api/photos?postId=999999';
        $result = $controller->delete();

        $this->assertSame(Response::NOT_FOUND, $controller->getStatus()['code']);
        $this->assertSame('Post not found', json_decode($result, true)['error']);
    }

    public function testDeleteRejectsNotOwnPost(): void {
        $owner = $this->createUser('deleterowner', 'deleterowner@example.com', 'Valid-Password123!');
        $other = $this->createUser('deleterother', 'deleterother@example.com', 'Valid-Password123!');
        $post = new Post('/media/deleter-not-own.jpg', $owner->id);
        $post->save();

        $controller = new PhotoApiController($other);
        $_SERVER['REQUEST_URI'] = '/api/photos?postId=' . $post->id;
        $result = $controller->delete();

        $this->assertSame(Response::FORBIDDEN, $controller->getStatus()['code']);
        $this->assertSame('Unauthorized to delete this post', json_decode($result, true)['error']);
    }

    public function testDeleteSucceeds(): void {
        $user = $this->createUser('deletersuccess', 'deletersuccess@example.com', 'Valid-Password123!');
        $post = new Post('/media/deleter-success.jpg', $user->id);
        $post->save();

        $controller = new PhotoApiController($user);
        $_SERVER['REQUEST_URI'] = '/api/photos?postId=' . $post->id;
        $result = $controller->delete();

        $this->assertSame(Response::OK, $controller->getStatus()['code']);
        $this->assertTrue(json_decode($result, true)['success']);
        $this->assertNull(Post::find($post->id));
    }

    // ===== getPhotos() ======================================================

    public function testGetPhotosRejectsInvalidOffsetOrLimit(): void {
        $user = $this->createUser('feedinvalid', 'feedinvalid@example.com', 'Valid-Password123!');
        $controller = new PhotoApiController($user);

        $_SERVER['REQUEST_URI'] = '/api/photos?offset=-1&limit=10';
        $controller->getPhotos();
        $this->assertSame(Response::BAD_REQUEST, $controller->getStatus()['code']);

        $_SERVER['REQUEST_URI'] = '/api/photos?offset=0&limit=51';
        $controller->getPhotos();
        $this->assertSame(Response::BAD_REQUEST, $controller->getStatus()['code']);
    }

    public function testGetPhotosClampsLimitWhenFewerPostsRemain(): void {
        $user = $this->createUser('feedclamp', 'feedclamp@example.com', 'Valid-Password123!');
        for ($i = 0; $i < 3; $i++) {
            $post = new Post("/media/feed-clamp-{$i}.jpg", $user->id);
            $post->save();
        }

        $controller = new PhotoApiController($user);
        $_SERVER['REQUEST_URI'] = '/api/photos?offset=0&limit=10';
        $result = $controller->getPhotos();
        $data = json_decode($result, true);

        $this->assertSame(Response::OK, $controller->getStatus()['code']);
        $this->assertSame(3, $data['count']);
        $this->assertSame(3, substr_count($data['html'], 'data-post-id'));
    }

    // 　===== getCurrentUserPhotos() ==========================================

    public function testGetCurrentUserPhotosRejectsInvalidOffsetOrLimit(): void {
        $user = $this->createUser('galleryinvalid', 'galleryinvalid@example.com', 'Valid-Password123!');
        $controller = new PhotoApiController($user);

        $_SERVER['REQUEST_URI'] = '/api/photos/me?offset=0&limit=0';
        $controller->getCurrentUserPhotos();

        $this->assertSame(Response::BAD_REQUEST, $controller->getStatus()['code']);
    }

    public function testGetCurrentUserPhotosClampsLimitWhenFewerPostsRemain(): void {
        $user = $this->createUser('clamp', 'clamp@example.com', 'Valid-Password123!');
        $other = $this->createUser('clampother', 'clampother@example.com', 'Valid-Password123!');

        for ($i = 0; $i < 2; $i++) {
            $post = new Post("/media/clamp-{$i}.jpg", $user->id);
            $post->save();
        }
        $otherPost = new Post('/media/clamp-other.jpg', $other->id);
        $otherPost->save();

        $controller = new PhotoApiController($user);
        $_SERVER['REQUEST_URI'] = '/api/photos/me?offset=0&limit=10';
        $result = $controller->getCurrentUserPhotos();
        $data = json_decode($result, true);

        $this->assertSame(Response::OK, $controller->getStatus()['code']);
        $this->assertSame(2, $data['count']);
        $this->assertSame(2, substr_count($data['html'], 'data-post-id'));
    }
}
