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
        $provisionerSettings = $serviceDefinition->getProvisionerSettings();

        $injector->define(
            $serviceClass,
            [ ':settings' => $serviceDefinition->getSettings() ]
        );

        // there will only be one instance of the service when the "share" setting is true (default)
        if (!isset($provisionerSettings['share']) || true === $provisionerSettings['share']) {
            $injector->share($serviceClass);
        }

        if (isset($provisionerSettings['alias'])) {
            $alias = $provisionerSettings['alias'];
            if (!is_string($alias) && !class_exists($alias)) {
                throw new ConfigException('Alias must be an existing fully qualified class or interface name.');
            }
            $injector->alias($alias, $serviceClass);
        }
    }
}
