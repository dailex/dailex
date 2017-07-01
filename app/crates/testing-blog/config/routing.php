<?php

use Testing\Blog\Article\Controller\ArticleController;

$app->mount('/blog', function ($blog) {
    $blog->get('/article', [ArticleController::class, 'read'])->bind('testing.blog.article');
});
