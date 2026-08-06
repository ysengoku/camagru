<?php

final class PostReactionsControllerTest extends DbTestCase {
    public function testTodo(): void {
        $this->markTestIncomplete('like()/removeLike()/getComments()/addComment()/deleteComment() tests not written yet.');
    }
}

// - [ ] like()/removeLike(): already-liked / not-liked rejection, likesCount increments/decrements correctly
// - [ ] getComments(): pagination correctness
// - [ ] addComment(): invalid post ID, empty content, post-not-found
// - [ ] deleteComment(): ownership check (403 for someone else's comment)