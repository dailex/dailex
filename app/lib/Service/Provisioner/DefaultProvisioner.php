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
    ) {
        $service = $serviceDefinition->getClass();
        $provisionerConfig = $serviceDefinition->getProvisioner();

        $injector->define(
            $service,
            [ ':settings' => $serviceDefinition->getSettings() ]
        );

        // there will only be one instance of the service when the "share" setting is true (default)
        if (!isset($provisionerConfig['settings']['share']) || true === $provisionerConfig['settings']['share']) {
            $injector->share($service);
        }

        if (isset($provisionerConfig['settings']['alias'])) {
            $alias = $provisionerConfig['settings']['alias'];
            if (!is_string($alias) && !class_exists($alias)) {
                throw new ConfigException('Alias must be an existing fully qualified class or interface name.');
            }
            $injector->alias($alias, $service);
        }
    }
}
