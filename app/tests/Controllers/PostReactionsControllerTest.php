<?php

final class PostReactionsControllerTest extends DbTestCase {
    protected function tearDown(): void {
        parent::tearDown();

        $db = Database::getInstance();
        $db->execute('SET FOREIGN_KEY_CHECKS=0');
        $db->execute('TRUNCATE TABLE posts');
        $db->execute('TRUNCATE TABLE likes');
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

    private function createPost(User $owner, string $imagePath = '/media/reaction-test.jpg'): Post {
        $post = new Post($imagePath, $owner->id);
        $post->save();

        return $post;
    }

    // ===== like() =============================================================

    public function testLikeRejectsInvalidPostId(): void {
        $user = $this->createUser('likeinvalidid', 'likeinvalidid@example.com', 'Valid-Password123!');
        $controller = new PostReactionsController($user);

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = ['postId' => 'not-a-number'];
        $result = $controller->like();

        $this->assertSame(Response::BAD_REQUEST, $controller->getStatus()['code']);
        $this->assertSame('Invalid post ID', json_decode($result, true)['error']);
    }

    public function testLikeSucceeds(): void {
        $owner = $this->createUser('likeowner', 'likeowner@example.com', 'Valid-Password123!');
        $liker = $this->createUser('likesuccess', 'likesuccess@example.com', 'Valid-Password123!');
        $post = $this->createPost($owner);

        $controller = new PostReactionsController($liker);
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = ['postId' => $post->id];
        $result = $controller->like();
        $data = json_decode($result, true);

        $this->assertSame(Response::CREATED, $controller->getStatus()['code']);
        $this->assertSame('Like added', $data['message']);
        $this->assertSame(1, $data['likesCount']);
    }

    public function testLikeRejectsAlreadyLiked(): void {
        $owner = $this->createUser('likedupowner', 'likedupowner@example.com', 'Valid-Password123!');
        $liker = $this->createUser('likeduplicate', 'likeduplicate@example.com', 'Valid-Password123!');
        $post = $this->createPost($owner);

        $controller = new PostReactionsController($liker);
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = ['postId' => $post->id];
        $controller->like();
        $result = $controller->like();

        $this->assertSame(Response::BAD_REQUEST, $controller->getStatus()['code']);
        $this->assertSame('The user has already liked this post', json_decode($result, true)['error']);
    }

    // ===== removeLike() =======================================================

    public function testRemoveLikeRejectsInvalidPostId(): void {
        $user = $this->createUser('removelikeinvalid', 'removelikeinvalid@example.com', 'Valid-Password123!');
        $controller = new PostReactionsController($user);

        $_SERVER['REQUEST_METHOD'] = 'DELETE';
        $_SERVER['REQUEST_URI'] = '/api/like';
        $result = $controller->removeLike();

        $this->assertSame(Response::BAD_REQUEST, $controller->getStatus()['code']);
        $this->assertSame('Invalid post ID', json_decode($result, true)['error']);
    }

    public function testRemoveLikeRejectsNotLiked(): void {
        $owner = $this->createUser('removenotlikedowner', 'removenotlikedowner@example.com', 'Valid-Password123!');
        $user = $this->createUser('removenotliked', 'removenotliked@example.com', 'Valid-Password123!');
        $post = $this->createPost($owner);

        $controller = new PostReactionsController($user);
        $_SERVER['REQUEST_METHOD'] = 'DELETE';
        $_SERVER['REQUEST_URI'] = '/api/like?postId=' . $post->id;
        $result = $controller->removeLike();

        $this->assertSame(Response::BAD_REQUEST, $controller->getStatus()['code']);
        $this->assertSame('The user has not liked this post. Cannot remove like.', json_decode($result, true)['error']);
    }

    public function testRemoveLikeSucceeds(): void {
        $owner = $this->createUser('removelikeowner', 'removelikeowner@example.com', 'Valid-Password123!');
        $user = $this->createUser('removelikesuccess', 'removelikesuccess@example.com', 'Valid-Password123!');
        $post = $this->createPost($owner);

        $controller = new PostReactionsController($user);
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = ['postId' => $post->id];
        $controller->like();

        $_SERVER['REQUEST_METHOD'] = 'DELETE';
        $_SERVER['REQUEST_URI'] = '/api/like?postId=' . $post->id;
        $result = $controller->removeLike();
        $data = json_decode($result, true);

        $this->assertSame(Response::OK, $controller->getStatus()['code']);
        $this->assertSame('Like removed', $data['message']);
        $this->assertSame(0, $data['likesCount']);
    }

    // ===== getComments() ======================================================

    public function testGetCommentsRejectsInvalidPostId(): void {
        $user = $this->createUser('commentsinvalidpost', 'commentsinvalidpost@example.com', 'Valid-Password123!');
        $controller = new PostReactionsController($user);

        $_SERVER['REQUEST_URI'] = '/api/comments?postId=not-a-number';
        $result = $controller->getComments();

        $this->assertSame(Response::BAD_REQUEST, $controller->getStatus()['code']);
        $this->assertSame('Invalid post ID', json_decode($result, true)['error']);
    }

    public function testGetCommentsRejectsInvalidOffsetOrLimit(): void {
        $owner = $this->createUser('commentlimitowner', 'commentlimitowner@example.com', 'Valid-Password123!');
        $post = $this->createPost($owner);
        $controller = new PostReactionsController($owner);

        $_SERVER['REQUEST_URI'] = '/api/comments?postId=' . $post->id . '&offset=0&limit=51';
        $result = $controller->getComments();

        $this->assertSame(Response::BAD_REQUEST, $controller->getStatus()['code']);
        $this->assertSame('Invalid offset or limit', json_decode($result, true)['error']);
    }

    public function testGetCommentsPaginationCorrectness(): void {
        $owner = $this->createUser('commentpageowner', 'commentpageowner@example.com', 'Valid-Password123!');
        $post = $this->createPost($owner);

        for ($i = 0; $i < 3; $i++) {
            $comment = new Comment($post->id, $owner->id, "Pagination comment {$i}");
            $comment->save();
        }

        $controller = new PostReactionsController($owner);
        $_SERVER['REQUEST_URI'] = '/api/comments?postId=' . $post->id . '&offset=0&limit=2';
        $result = $controller->getComments();
        $data = json_decode($result, true);

        $this->assertSame(Response::OK, $controller->getStatus()['code']);
        $this->assertSame(3, $data['count']);
        $this->assertSame(2, substr_count($data['html'], 'class="comment '));
    }

    // ===== addComment() =======================================================

    public function testAddCommentRejectsInvalidPostId(): void {
        $user = $this->createUser('addcommentbadpost', 'addcommentbadpost@example.com', 'Valid-Password123!');
        $controller = new PostReactionsController($user);

        $_POST = ['postId' => 'not-a-number', 'content' => 'Hello'];
        $result = $controller->addComment();

        $this->assertSame(Response::BAD_REQUEST, $controller->getStatus()['code']);
        $this->assertSame('Invalid post ID or empty content', json_decode($result, true)['error']);
    }

    public function testAddCommentRejectsEmptyContent(): void {
        $owner = $this->createUser('addcommentemptyowner', 'addcommentemptyowner@example.com', 'Valid-Password123!');
        $post = $this->createPost($owner);
        $controller = new PostReactionsController($owner);

        $_POST = ['postId' => $post->id, 'content' => '   '];
        $result = $controller->addComment();

        $this->assertSame(Response::BAD_REQUEST, $controller->getStatus()['code']);
        $this->assertSame('Invalid post ID or empty content', json_decode($result, true)['error']);
    }

    public function testAddCommentRejectsPostNotFound(): void {
        $user = $this->createUser('addcommentnotfound', 'addcommentnotfound@example.com', 'Valid-Password123!');
        $controller = new PostReactionsController($user);

        $_POST = ['postId' => 999999, 'content' => 'Hello'];
        $result = $controller->addComment();

        $this->assertSame(Response::NOT_FOUND, $controller->getStatus()['code']);
        $this->assertSame('Post not found', json_decode($result, true)['error']);
    }

    public function testAddCommentSucceeds(): void {
        $owner = $this->createUser('addcommentsuccessown', 'addcommentsuccessown@example.com', 'Valid-Password123!');
        $post = $this->createPost($owner);
        $controller = new PostReactionsController($owner);

        $_POST = ['postId' => $post->id, 'content' => 'A brand new comment'];
        $result = $controller->addComment();
        $data = json_decode($result, true);

        $this->assertSame(Response::CREATED, $controller->getStatus()['code']);
        $this->assertSame('Comment added', $data['message']);
        $this->assertStringContainsString('A brand new comment', $data['html']);
        $this->assertSame(1, $data['commentCount']);
    }

    // ===== deleteComment() ====================================================

    public function testDeleteCommentRejectsInvalidId(): void {
        $user = $this->createUser('deletecommentinvalid', 'deletecommentinvalid@example.com', 'Valid-Password123!');
        $controller = new PostReactionsController($user);

        $_SERVER['REQUEST_URI'] = '/api/comments?commentId=not-a-number';
        $result = $controller->deleteComment();

        $this->assertSame(Response::BAD_REQUEST, $controller->getStatus()['code']);
        $this->assertSame('Invalid comment ID', json_decode($result, true)['error']);
    }

    public function testDeleteCommentRejectsNotOwnComment(): void {
        $owner = $this->createUser('deletecommentowner', 'deletecommentowner@example.com', 'Valid-Password123!');
        $other = $this->createUser('deletecommentother', 'deletecommentother@example.com', 'Valid-Password123!');
        $post = $this->createPost($owner);
        $comment = new Comment($post->id, $owner->id, 'Owned by owner');
        $comment->save();

        $controller = new PostReactionsController($other);
        $_SERVER['REQUEST_URI'] = '/api/comments?commentId=' . $comment->id;
        $result = $controller->deleteComment();

        $this->assertSame(Response::FORBIDDEN, $controller->getStatus()['code']);
        $this->assertSame('Comment not found or user not authorized to delete it', json_decode($result, true)['error']);
    }

    public function testDeleteCommentSucceeds(): void {
        $owner = $this->createUser('deletecommentsuccess', 'deletecommentsuccess@example.com', 'Valid-Password123!');
        $post = $this->createPost($owner);
        $comment = new Comment($post->id, $owner->id, 'Comment to delete');
        $comment->save();

        $controller = new PostReactionsController($owner);
        $_SERVER['REQUEST_URI'] = '/api/comments?commentId=' . $comment->id;
        $result = $controller->deleteComment();
        $data = json_decode($result, true);

        $this->assertSame(Response::OK, $controller->getStatus()['code']);
        $this->assertSame('Comment deleted', $data['message']);
        $this->assertSame(0, $data['commentCount']);
        $this->assertNull(Comment::find($comment->id));
    }
}
