<?php

namespace Dailex\Service\Provisioner;

use Auryn\Injector;
use Daikon\Config\ConfigProviderInterface;
use Daikon\Cqrs\EventStore\PersistenceAdapterMap;
use Daikon\Cqrs\EventStore\UnitOfWork;
use Daikon\Cqrs\EventStore\UnitOfWorkMap;
use Dailex\Infrastructure\DataAccess\Connector\ConnectorMap;
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
        $uowConfigs = $configProvider->get('data_access.units_of_work');

        $factory = function (PersistenceAdapterMap $persistenceAdapaterMap) use ($injector, $uowConfigs) {
            $unitsOfWork = [];
            foreach ($uowConfigs as $uowName => $uowConfig) {
                $unitsOfWork[$uowName] = new UnitOfWork(
                    $uowConfig['aggregate_root'],
                    $persistenceAdapaterMap->get($uowConfig['persistence_adapter']),
                    new \Daikon\Cqrs\EventStore\NoopStreamProcessor
                );
            }
            return new UnitOfWorkMap($unitsOfWork);
        };

        $injector
            ->share($serviceClass)
            ->delegate($serviceClass, $factory);
    }
}
