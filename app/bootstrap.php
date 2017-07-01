<?php

use Silex\Application;

$app = new Application;
$app['version'] = $appVersion;
$app['debug'] = filter_var($appDebug, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
$app['config'] = [
    'version' => $app['version'],
    'context' => $appContext,
    'env' => $appEnv,
    'debug' => $app['debug'],
    'prefix' => $hostPrefix,
    'base_dir' => dirname(__DIR__),
    'dir' => __DIR__,
    'crate_dir' => __DIR__.'/crates',
    'config_dir' => __DIR__.'/config',
    'secrets_dir' => $secretsDir,
    'log_dir' => dirname(__DIR__).'/var/logs',
    'cache_dir' => dirname(__DIR__).'/var/cache',
    'dailex' => [
        'dir' => dirname(__DIR__),
        'config_dir' => __DIR__.'/config/default'
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
