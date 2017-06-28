<?php

namespace Dailex\Service\Provisioner;

use Auryn\Injector;
use Daikon\Config\ConfigProviderInterface;
use Dailex\Exception\RuntimeException;
use Pimple\Container;

class DefaultProvisioner implements ProvisionerInterface
{
    public function provision(
        Container $app,
        Injector $injector,
        ConfigProviderInterface $configProvider
    ) {
        $service = $serviceDefinition->getClass();
        $state = [ ':config' => $serviceDefinition->getConfig() ];

        $injector->define($service, $state);
        // there will only be one instance of the service when the "share" setting is true (default)
        if ($provisionerSettings->get('share', true) === true) {
            $injector->share($service);
        }

        if ($provisionerSettings->has('alias')) {
            $alias = $provisionerSettings->get('alias');
            if (!is_string($alias) && !class_exists($alias)) {
                throw new RuntimeException('Alias given must be an existing class or interface name (fully qualified).');
            }
            $injector->alias($alias, $service);
        }
    }
}
