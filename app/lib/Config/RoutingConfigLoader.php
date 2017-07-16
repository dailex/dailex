<?php

namespace Dailex\Config;

use Daikon\Config\ConfigLoaderInterface;
use Daikon\Config\ConfigProviderInterface;
use Silex\Application;

final class RoutingConfigLoader implements ConfigLoaderInterface
{
    private $app;

    private $configProvider;

    public function __construct(Application $app, ConfigProviderInterface $configProvider)
    {
        $this->app = $app;
        $this->configProvider = $configProvider;
    }

    public function load(array $locations, array $sources): array
    {
        $app = $this->app;
        $configProvider = $this->configProvider;
        $loadedConfigs = [];
        foreach ($locations as $location) {
            if (substr($location, -1) !== '/') {
                $location .= '/';
            }
            foreach ($sources as $source) {
                $filepath = $location.$source;
                if (is_readable($filepath)) {
                    require $filepath;
                }
            }
        }
        return $loadedConfigs;
    }
}
