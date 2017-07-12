<?php

namespace Dailex\Service\Provisioner;

use Auryn\Injector;
use Daikon\Config\ConfigProviderInterface;
use Dailex\Crate\Crate;
use Dailex\Service\ServiceDefinitionInterface;
use Pimple\Container;

final class CrateMapProvisioner implements ProvisionerInterface
{
    public function provision(
        Container $app,
        Injector $injector,
        ConfigProviderInterface $configProvider,
        ServiceDefinitionInterface $serviceDefinition
    ): void {
        $serviceClass = $serviceDefinition->getServiceClass();
        $crateConfigs = $configProvider->get('crates', []);

        $factory = function () use ($serviceClass, $crateConfigs) {
            $crates = [];
            foreach ($crateConfigs as $crateName => $crateConfig) {
                $crates[$crateName] = new Crate($crateConfig);
            }
            return new $serviceClass($crates);
        };

        $injector
            ->share($serviceClass)
            ->delegate($serviceClass, $factory);
    }
}
