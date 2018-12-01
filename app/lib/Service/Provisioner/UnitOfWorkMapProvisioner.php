<?php

namespace Dailex\Service\Provisioner;

use Auryn\Injector;
use Daikon\Config\ConfigProviderInterface;
use Daikon\EventSourcing\EventStore\UnitOfWork;
use Dailex\Infrastructure\StreamStorageMap;
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
        $uowConfigs = $configProvider->get('databases.units_of_work', []);

        $factory = function (StreamStorageMap $streamStorageMap) use ($uowConfigs, $serviceClass) {
            $unitsOfWork = [];
            foreach ($uowConfigs as $uowName => $uowConfig) {
                $unitsOfWork[$uowName] = new UnitOfWork(
                    $uowConfig['aggregate_root'],
                    $streamStorageMap->get($uowConfig['stream_store'])
                );
            }
            return new $serviceClass($unitsOfWork);
        };

        $injector
            ->share($serviceClass)
            ->delegate($serviceClass, $factory);
    }
}
