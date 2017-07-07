<?php

use Dailex\Console\Console;
use Dailex\Console\Command\Project\ConfigureProject;
use Dailex\Console\Command\Route\ListRoutes;
use Symfony\Component\Console\Input\ArgvInput;

ini_set('display_errors', true);

$basedir = getcwd() ?: dirname(__DIR__);
require_once $basedir.'/vendor/autoload.php';

$appContext = 'console';

$appVersion = getEnv('APP_VERSION') ?: 'master';
$appEnv = (new ArgvInput)->getParameterOption([ '--env', '-e' ], getenv('APP_ENV') ?: 'dev');
$appDebug = (new ArgvInput)->getParameterOption('--debug', getenv('APP_DEBUG') ?: true);
$hostPrefix = (new ArgvInput)->getParameterOption([ '--host', '-h' ], getenv('HOST_PREFIX'));
$secretsDir = getenv('SECRETS_DIR') ?: '/usr/local/env';

require $basedir.'/app/bootstrap.php';

$app->boot();
$app->flush();

$commands = [
    ConfigureProject::class,
    ListRoutes::class
];

set_time_limit(0);

$app['dailex.service_locator']->make(
    Console::class,
    [':app' => $app, ':commands' => $commands]
)->run();
