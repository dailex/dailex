<?php

namespace Dailex\Service\Provisioner;

use Auryn\Injector;
use Daikon\Config\ConfigProviderInterface;
use Daikon\Cqrs\EventStore\StreamStoreMap;
use Daikon\Cqrs\EventStore\UnitOfWork;
use Daikon\Cqrs\EventStore\UnitOfWorkMap;
use Daikon\Dbal\Connector\ConnectorMap;
use Dailex\Service\ServiceDefinitionInterface;
use Pimple\Container;

final class UnitOfWorkMapProvisioner implements ProvisionerInterface
{
    public function provision(
        Container $app,
        Injector $injector,
        ConfigProviderInterface $configProvider,
        ServiceDefinitionInterface $serviceDefinition
    ): void {
        $serviceClass = $serviceDefinition->getServiceClass();
        $uowConfigs = $configProvider->get('databases.units_of_work');

        $factory = function (StreamStoreMap $streamStoreMap) use ($uowConfigs, $serviceClass) {
            $unitsOfWork = [];
            foreach ($uowConfigs as $uowName => $uowConfig) {
                $unitsOfWork[$uowName] = new UnitOfWork(
                    $uowConfig['aggregate_root'],
                    $streamStoreMap->get($uowConfig['stream_store']),
                    new \Daikon\Cqrs\EventStore\NoopStreamProcessor
                );
            }
            return new $serviceClass($unitsOfWork);
        };

        $injector
            ->share($serviceClass)
            ->delegate($serviceClass, $factory);
    }
}
