<?php

use Dailex\Article\Controller\ArticleController;

$app->get('/', function () use ($app) {
    return $app['twig']->render('@dailex/home.html.twig');
})->bind('home');

$app->get('/article', [ArticleController::class, 'read']);
