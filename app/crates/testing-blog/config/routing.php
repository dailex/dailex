<?php

use Testing\Blog\Article\Controller\ArticleController;

$app->mount('/blog', function ($routing) {
    $app->get('/article', [ArticleController::class, 'read'])->bind($this->getPrefix().'.article');
});
