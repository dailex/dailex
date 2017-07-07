<?php

namespace Dailex\Service\Provisioner;

use Auryn\Injector;
use Daikon\Config\ConfigProviderInterface;
use Daikon\Dbal\Connector\ConnectorMap;
use Daikon\EventSourcing\EventStore\StreamStoreMap;
use Daikon\EventSourcing\EventStore\UnitOfWork;
use Daikon\EventSourcing\EventStore\UnitOfWorkMap;
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
                    $streamStoreMap->get($uowConfig['stream_store'])
                );
            }
            return new $serviceClass($unitsOfWork);
        };

        $injector
            ->share($serviceClass)
            ->delegate($serviceClass, $factory);
    }
}
