<?php

use Silex\Application;

$projectConfigDir = __DIR__.'/config';
$application = new Application;
$settings = [
    'appVersion' => $appVersion,
    'appContext' => $appContext,
    'appEnv' => $appEnv,
    'appDebug' => filter_var($appDebug, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
    'hostPrefix' => $hostPrefix,
    'core' => [
        'config_dir' => __DIR__.'/config/default',
        'dir' => dirname(__DIR__)
    ],
    'project' => [
        'config_dir' => $projectConfigDir,
        'dir' => dirname(__DIR__),
        'local_config_dir' => $localConfigDir,
        'log_dir' => dirname(__DIR__).'/var/logs'
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
$app = $bootstrap($application, $settings);
