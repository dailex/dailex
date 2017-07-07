<?php

namespace Dailex\Console;

use Daikon\Config\ConfigProviderInterface;
use Dailex\Service\ServiceLocatorInterface;
use Silex\Application as Silex;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputOption;

final class Console extends Application
{
    private $app;

    private $configProvider;

    public static function getLogo()
    {
        return <<<ASCII

 ______   _______  ___   ___      _______  __   __
|      | |   _   ||   | |   |    |       ||  |_|  |
|  _    ||  |_|  ||   | |   |    |    ___||       |
| | |   ||       ||   | |   |    |   |___ |       |
| |_|   ||       ||   | |   |___ |    ___| |     |
|       ||   _   ||   | |       ||   |___ |   _   |
|______| |__| |__||___| |_______||_______||__| |__|

ASCII;
    }

    public function __construct(
        Silex $app,
        ConfigProviderInterface $configProvider,
        ServiceLocatorInterface $serviceLocator,
        array $commands = []
    ) {
        $this->app = $app;
        $this->configProvider = $configProvider;

        parent::__construct(
            $configProvider->get('project.name'),
            sprintf('%s@%s', $configProvider->get('project.version'), $configProvider->get('app.env'))
        );

        $this->getDefinition()->addOption(
            new InputOption('--env', '-e', InputOption::VALUE_REQUIRED, 'The environment name.', 'dev')
        );

        foreach (array_map([$serviceLocator, 'make'], $commands) as $command) {
            $this->add($command);
        }

        $this->setDispatcher($app['dispatcher']);
    }

    public function getHelp()
    {
        return self::getLogo().parent::getHelp();
    }

    public function getContainer()
    {
        return $this->app;
    }
}
