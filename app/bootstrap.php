<?php

use Silex\Application;

$projectConfigDir = __DIR__.'/config';
$app = new Application;
$app['version'] = $appVersion;
$app['debug'] = filter_var($appDebug, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
$app['config'] = [
    'version' => $app['version'],
    'context' => $appContext,
    'env' => $appEnv,
    'debug' => $app['debug'],
    'prefix' => $hostPrefix,
    'dir' => dirname(__DIR__),
    'config_dir' => $projectConfigDir,
    'secrets_dir' => $secretsDir,
    'log_dir' => dirname(__DIR__).'/var/logs',
    'cache_dir' => dirname(__DIR__).'/var/cache',
    'dailex' => [
        'config_dir' => __DIR__.'/config/default',
        'dir' => dirname(__DIR__)
    ]
];

// execute context specific bootstrap
$customContextBootstrap = $projectConfigDir."/bootstrap.$appContext.php";
if (is_readable($customContextBootstrap)) {
    return require $customContextBootstrap;
}

// default bootstrap attempt
$bootstrapClass = 'Dailex\\Bootstrap\\'.ucfirst($appContext).'Bootstrap';
$bootstrap = new $bootstrapClass;
$bootstrap($app);
