<?php

use Testing\Blog\Article\Controller\ArticleController;

$cratePrefix = 'testing-blog';
$mount = $configProvider->get('crates.'.$cratePrefix.'.mount');

$app->mount($mount, function ($blog) use ($cratePrefix) {
    $blog->get('/article', [ArticleController::class, 'read'])->bind($cratePrefix.'.article');
});
