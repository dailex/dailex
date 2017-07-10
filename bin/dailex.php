<?php

use Dailex\Console\Command\Migrate\ListTargets;
use Dailex\Console\Command\Project\ConfigureProject;
use Dailex\Console\Command\Route\ListRoutes;
use Dailex\Console\Command\Migrate\MigrateDown;
use Dailex\Console\Command\Migrate\MigrateUp;
use Dailex\Console\Console;
use Symfony\Component\Console\Input\ArgvInput;

ini_set('display_errors', true);

$basedir = getcwd() ?: dirname(__DIR__);
require_once $basedir.'/vendor/autoload.php';

$appContext = 'console';

$appVersion = getEnv('APP_VERSION') ?: 'master';
$appEnv = (new ArgvInput)->getParameterOption([ '--env', '-e' ], getenv('APP_ENV') ?: 'dev');
$appDebug = (new ArgvInput)->getParameterOption('--debug', getenv('APP_DEBUG') ?: true);
$secretsDir = getenv('SECRETS_DIR') ?: '/usr/local/env';

require $basedir.'/app/bootstrap.php';

$app->boot();
$app->flush();

$commands = [
    ConfigureProject::class,
    ListRoutes::class,
    ListTargets::class,
    MigrateDown::class,
    MigrateUp::class
];

set_time_limit(0);

$app['dailex.service_locator']->make(
    Console::class,
    [':app' => $app, ':commands' => $commands]
)->run();
