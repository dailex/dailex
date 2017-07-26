<?php

namespace Dailex\Service\Provisioner;

use Auryn\Injector;
use Daikon\Config\ConfigProviderInterface;
use Dailex\Exception\ConfigException;
use Dailex\Service\ServiceDefinitionInterface;
use Pimple\Container;

final class DefaultProvisioner implements ProvisionerInterface
{
    public function provision(
        Container $app,
        Injector $injector,
        ConfigProviderInterface $configProvider,
        ServiceDefinitionInterface $serviceDefinition
    ): void {
        $serviceClass = $serviceDefinition->getServiceClass();
        $settings = $serviceDefinition->getSettings();

        $injector->define($serviceClass, [':settings' => $settings]);

        // there will only be one instance of the service when the "share" setting is true (default)
        if (!isset($settings['_share']) || true === $settings['_share']) {
            $injector->share($serviceClass);
        }

        if (isset($settings['_alias'])) {
            $alias = $settings['_alias'];
            if (!is_string($alias) && !class_exists($alias)) {
                throw new ConfigException('Alias must be an existing fully qualified class or interface name.');
            }
            $injector->alias($alias, $serviceClass);
        }
    }
}
