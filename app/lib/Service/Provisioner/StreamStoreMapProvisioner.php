<?php

namespace Dailex\Service\Provisioner;

use Auryn\Injector;
use Daikon\Config\ConfigProviderInterface;
use Daikon\Cqrs\EventStore\StreamStoreMap;
use Daikon\Dbal\Storage\StorageAdapterMap;
use Dailex\Exception\RuntimeException;
use Dailex\Service\ServiceDefinitionInterface;
use Pimple\Container;

final class StreamStoreMapProvisioner implements ProvisionerInterface
{
    public function provision(
        Container $app,
        Injector $injector,
        ConfigProviderInterface $configProvider,
        ServiceDefinitionInterface $serviceDefinition
    ): void {
        $serviceClass = $serviceDefinition->getServiceClass();
        $adapterConfigs = $configProvider->get('databases.stream_stores');

        $factory = function (StorageAdapterMap $storageAdapterMap) use ($injector, $adapterConfigs, $serviceClass) {
            $adapters = [];
            foreach ($adapterConfigs as $adapterName => $adapterConfigs) {
                $adapterClass = $adapterConfigs['class'];
                $adapters[$adapterName] = $injector->make(
                    $adapterClass,
                    [':storageAdapter' => $storageAdapterMap->get($adapterConfigs['storage_adapter'])]
                );
            }
            return new $serviceClass($adapters);
        };

        $injector
            ->share($serviceClass)
            ->delegate($serviceClass, $factory);
    }
}
