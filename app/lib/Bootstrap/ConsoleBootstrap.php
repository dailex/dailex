<?php

namespace Dailex\Bootstrap;

use Auryn\Injector;
use Auryn\StandardReflector;
use Daikon\Config\ArrayConfigLoader;
use Daikon\Config\ConfigProvider;
use Daikon\Config\ConfigProviderInterface;
use Daikon\Config\ConfigProviderParams;
use Daikon\Config\YamlConfigLoader;
use Dailex\Service\ServiceProvider;
use Dailex\Service\ServiceProvisioner;
use Silex\Application;
use Silex\Provider\AssetServiceProvider;
use Silex\Provider\ValidatorServiceProvider;

class ConsoleBootstrap implements BootstrapInterface
{
    private $injector;

    private $configProvider;

    public function __construct()
    {
        // @todo use caching reflector?
        $this->injector = new Injector(new StandardReflector);
    }

    public function __invoke(Application $app): void
    {
        $this->bootstrapConfig($app);
        $this->boostrapServices($app);

        // Update request context from environment for URL generation
        if ($hostName = getenv('HOST_NAME')) {
            $app['request_context']->setHost($hostName);
        }

        if ($hostScheme = getenv('HOST_SCHEME')) {
            $app['request_context']->setScheme($hostScheme);
        }

        if ($hostHttpPort = getenv('HOST_HTTP_PORT')) {
            $app['request_context']->setHttpPort($hostHttpPort);
        }

        if ($hostHttpsPort = getenv('HOST_HTTPS_PORT')) {
            $app['request_context']->setHttpsPort($hostHttpsPort);
        }
    }

    private function bootstrapConfig(Application $app): void
    {
        $this->configProvider = new ConfigProvider(
            new ConfigProviderParams(
                array_merge(
                    [
                        'app' => [
                            'loader' => ArrayConfigLoader::class,
                            'sources' => $app['config']
                        ]
                    ],
                    (new YamlConfigLoader)->load(
                        [
                            $app['config']['dailex']['config_dir'],
                            $app['config']['config_dir']
                        ],
                        ['loaders.yml']
                    )
                )
            )
        );

        $this->injector
            ->share($this->configProvider)
            ->alias(ConfigProviderInterface::class, ConfigProvider::class);
    }

    private function boostrapServices(Application $app): void
    {
        $serviceProvisioner = new ServiceProvisioner($app, $this->injector, $this->configProvider);
        $app->register(new ServiceProvider($serviceProvisioner));
        $app->register(new AssetServiceProvider);
        $app->register(new ValidatorServiceProvider);
    }
}
