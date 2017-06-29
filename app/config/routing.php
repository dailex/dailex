<?php

$app->get('/', function () use ($app) {
    return $app['twig']->render('@dailex/home.html.twig');
})->bind('home');