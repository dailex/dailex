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
        $settings = $serviceDefinition->getSettings();

        if (!isset($settings['_app_key'])) {
            throw new ConfigException('Provisioner requires "_app_key" setting.');
        }

        $appKey = $settings['_app_key'];
        $injector->delegate($serviceClass, function () use ($app, $appKey) {
            return $app[$appKey];
        })->share($serviceClass);

        if (isset($settings['_alias'])) {
            $alias = $settings['_alias'];
            if (!is_string($alias) && !class_exists($alias)) {
                throw new ConfigException('Alias must be an existing fully qualified class or interface name.');
            }
            $injector->alias($alias, $serviceClass);
        }
    }
}
