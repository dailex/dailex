<?php

namespace Dailex\Console;

use Daikon\Config\ConfigProviderInterface;
use Silex\Application as SilexApp;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputOption;

class App extends Application
{
    protected $app;

    protected $configProvider;

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

    public function __construct(SilexApp $app, array $appCommands, ConfigProviderInterface $configProvider)
    {
        $this->app = $app;
        $this->configProvider = $configProvider;

        parent::__construct('dailex', $configProvider->get('app.version'));

        $this->getDefinition()->addOption(
            new InputOption('--env', '-e', InputOption::VALUE_REQUIRED, 'The Environment name.', 'dev')
        );

        foreach (array_map([ $app['dailex.service_locator'], 'make'], $appCommands) as $command) {
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
