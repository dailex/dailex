<?php

namespace Dailex\Service\Provisioner;

use Auryn\Injector;
use Daikon\Config\ConfigProviderInterface;
use Daikon\Dbal\Connector\ConnectorMap;
use Daikon\Dbal\Storage\StorageAdapterMap;
use Dailex\Exception\RuntimeException;
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
        $adapterConfigs = $configProvider->get('databases.storage_adapters');

        $factory = function (ConnectorMap $connectorMap) use ($injector, $adapterConfigs, $serviceClass) {
            $adapters = [];
            foreach ($adapterConfigs as $adapterName => $adapterConfigs) {
                $adapterClass = $adapterConfigs['class'];
                $adapters[$adapterName] = $injector->make(
                    $adapterClass,
                    [
                        ':connector' => $connectorMap->get($adapterConfigs['connector']),
                        ':settings' => $adapterConfigs['settings'] ?? []
                    ]
                );
            }
            return new $serviceClass($adapters);
        };

        $injector
            ->share($serviceClass)
            ->delegate($serviceClass, $factory);
    }
}
