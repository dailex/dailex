<?php

namespace Dailex\Config\Loader;

use Daikon\Config\ConfigLoaderInterface;
use Symfony\Component\Yaml\Yaml;

final class ServiceConfigLoader implements ConfigLoaderInterface
{
    /**
     * @var Yaml
     */
    private $yamlParser;

    /**
     * @param Yaml|null $yamlParser
     */
    public function __construct(Yaml $yamlParser = null)
    {
        $this->yamlParser = $yamlParser ?? new Yaml;
    }

    /**
     * @param string[] $locations
     * @param string[] $sources
     * @return mixed[]
     */
    public function load(array $locations, array $sources): array
    {
        $loadedConfigs = [];
        foreach ($locations as $location) {
            if (substr($location, -1) !== "/") {
                $location .= "/";
            }
            foreach ($sources as $source) {
                $filepath = $location.$source;
                if (is_readable($filepath)) {
                    $loadedConfigs = array_replace_recursive(
                        $loadedConfigs,
                        $this->yamlParser->parse(file_get_contents($filepath))
                    );
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
