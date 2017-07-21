<?php

namespace Dailex\Bootstrap;

use Auryn\Injector;
use Daikon\Config\ArrayConfigLoader;
use Daikon\Config\ConfigProvider;
use Daikon\Config\ConfigProviderInterface;
use Daikon\Config\ConfigProviderParams;
use Daikon\Config\YamlConfigLoader;
use Silex\Application;

abstract class Bootstrap implements BootstrapInterface
{
    protected $injector;

    protected $configProvider;

    public function __construct(Injector $injector)
    {
        $this->injector = $injector;
    }

    protected function bootstrapConfig(Application $app): void
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
}
