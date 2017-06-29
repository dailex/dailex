<?php

namespace Dailex\Service\Provisioner;

use Auryn\Injector;
use Daikon\Config\ConfigProviderInterface;
use Dailex\Exception\ConfigException;
use Dailex\Service\ServiceDefinitionInterface;
use Pimple\Container;

class SilexServiceProvisioner implements ProvisionerInterface
{
    public function provision(
        Container $app,
        Injector $injector,
        ConfigProviderInterface $configProvider,
        ServiceDefinitionInterface $serviceDefinition
    ) {
        $service = $serviceDefinition->getClass();
        $provisionerConfig = $serviceDefinition->getProvisioner();

        if (!isset($provisionerConfig['settings']['app_key'])) {
            throw new ConfigException('Provisioner requires "app_key" setting.');
        }

        $appKey = $provisionerConfig['settings']['app_key'];
        $injector->delegate($service, function () use ($app, $appKey) {
            return $app[$appKey];
        })->share($service);

        if (isset($provisionerConfig['settings']['alias'])) {
            $alias = $provisionerConfig['settings']['alias'];
            if (!is_string($alias) && !class_exists($alias)) {
                throw new ConfigException('Alias must be an existing fully qualified class or interface name.');
            }
            $injector->alias($alias, $service);
        }
    }
}
