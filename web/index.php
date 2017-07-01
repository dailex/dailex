<?php
ini_set('display_errors', true);
ini_set('xdebug.default_enable', true);

$appContext = 'web';

$appVersion = getEnv('APP_VERSION') ?: 'master';
$appEnv = getenv('APP_ENV') ?: 'dev';
$appDebug = getenv('APP_DEBUG') ?: true;
$hostPrefix = getenv('HOST_PREFIX');
$secretsDir = getenv('SECRETS_DIR') ?: '/usr/local/env';

require_once __DIR__.'/../vendor/autoload.php';
require_once __DIR__.'/../app/bootstrap.php';

$app->run();
