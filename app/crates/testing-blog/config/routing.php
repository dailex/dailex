<?php

use Testing\Blog\Article\Controller\CollectionController;
use Testing\Blog\Article\Controller\ResourceController;

$cratePrefix = 'testing-blog';
$mount = $configProvider->get('crates.'.$cratePrefix.'.mount');

$app->mount($mount, function ($blog) use ($cratePrefix) {
    $blog->get('/articles', [CollectionController::class, 'write'])->bind($cratePrefix.'.articles');
    $blog->get('/articles/{articleId}', [ResourceController::class, 'write'])->bind($cratePrefix.'.articles.resource');
});
