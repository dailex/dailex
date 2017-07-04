<?php

namespace Dailex\Service\Provisioner;

use Auryn\Injector;
use Daikon\Config\ConfigProviderInterface;
use Dailex\Exception\RuntimeException;
use Dailex\Infrastructure\DataAccess\Storage\StorageAdapterMap;
use Dailex\Infrastructure\DataAccess\Connector\ConnectorMap;
use Dailex\Service\ServiceDefinitionInterface;
use Pimple\Container;

final class StorageAdapterMapProvisioner implements ProvisionerInterface
{
    public function provision(
        Container $app,
        Injector $injector,
        ConfigProviderInterface $configProvider,
        ServiceDefinitionInterface $serviceDefinition
    ): void {
        $serviceClass = $serviceDefinition->getServiceClass();
        $adapterConfigs = $configProvider->get('data_access.storage_adapters');

        $factory = function (ConnectorMap $connectorMap) use ($injector, $adapterConfigs, $serviceClass) {
            $adapters = [];
            foreach ($adapterConfigs as $adapterName => $adapterConfigs) {
                $adapterClass = $adapterConfigs['class'];
                $adapters[$adapterName] = $injector->make(
                    $adapterClass,
                    [':connector' => $connectorMap->get($adapterConfigs['connection'])]
                );
            }
            return new $serviceClass($adapters);
        };

        $injector
            ->share($serviceClass)
            ->delegate($serviceClass, $factory);
    }
}
