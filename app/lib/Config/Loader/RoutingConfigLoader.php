<?php

namespace Dailex\Config\Loader;

use Daikon\Config\ConfigLoaderInterface;
use Silex\Application;

final class RoutingConfigLoader implements ConfigLoaderInterface
{
    /**
     * @var Application
     */
    private $app;

    /**
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * @param string[] $locations
     * @param string[] $sources
     * @return mixed[]
     */
    public function load(array $locations, array $sources): array
    {
        $app = $this->app;
        $loadedConfigs = [];
        foreach ($locations as $location) {
            if (substr($location, -1) !== "/") {
                $location .= "/";
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

    /**
     * @param mixed[] $config
     * @return string
     */
    public function serialize(array $config): string
    {
        // not implemented yet
        return '';
    }

    /**
     * @param string $serializedConfig
     * @return mixed[]
     */
    public function deserialize(string $serializedConfig): array
    {
        // not implemented yet
        return [];
    }
}
