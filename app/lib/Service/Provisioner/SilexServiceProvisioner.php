<?php

namespace Dailex\Service\Provisioner;

use Auryn\Injector;
use Daikon\Config\ConfigProviderInterface;
use Dailex\Exception\ConfigException;
use Dailex\Service\ServiceDefinitionInterface;
use Pimple\Container;

final class SilexServiceProvisioner implements ProvisionerInterface
{
    public function provision(
        Container $app,
        Injector $injector,
        ConfigProviderInterface $configProvider,
        ServiceDefinitionInterface $serviceDefinition
    ): void {
        $serviceClass = $serviceDefinition->getServiceClass();
        $provisionerSettings = $serviceDefinition->getProvisionerSettings();

        if (!isset($provisionerSettings['app_key'])) {
            throw new ConfigException('Provisioner requires "app_key" setting.');
        }

        $appKey = $provisionerSettings['app_key'];
        $injector->delegate($serviceClass, function () use ($app, $appKey) {
            return $app[$appKey];
        })->share($serviceClass);

        if (isset($provisionerSettings['alias'])) {
            $alias = $provisionerSettings['alias'];
            if (!is_string($alias) && !class_exists($alias)) {
                throw new ConfigException('Alias must be an existing fully qualified class or interface name.');
            }
            $injector->alias($alias, $serviceClass);
        }
    }
}
