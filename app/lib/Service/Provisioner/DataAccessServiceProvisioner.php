<?php

namespace Dailex\Service\Provisioner;

use Auryn\Injector;
use Daikon\Config\ConfigProviderInterface;
use Daikon\Cqrs\EventStore\UnitOfWork;
use Daikon\Cqrs\EventStore\UnitOfWorkMap;
use Dailex\Service\ServiceDefinitionInterface;
use Pimple\Container;

final class DataAccessServiceProvisioner implements ProvisionerInterface
{
    public function provision(
        Container $app,
        Injector $injector,
        ConfigProviderInterface $configProvider,
        ServiceDefinitionInterface $serviceDefinition
    ): void {
        $serviceClass = $serviceDefinition->getServiceClass();
        $dataAccessConfig = $configProvider->get('data_access');

        $this->registerUowMapDelegate($injector, $dataAccessConfig['units_of_work']);

        $injector
            ->share($serviceClass)
            ->alias(DataAccessServiceInterface::CLASS, $serviceClass);
    }

    private function registerUowMapDelegate(Injector $injector, array $uowConfigs)
    {
        $factory = function () use ($uowConfigs) {
            foreach ($uowConfigs as $uowName => $uowConfig) {
                $unitsOfWork[$uowName] = new UnitOfWork(
                    $uowConfig['dependencies']['aggregate_root'],
                    new \Dailex\Util\EchoPersistenceAdapter,
                    new \Daikon\Cqrs\EventStore\NoopStreamProcessor
                );
            }
            return new UnitOfWorkMap($unitsOfWork ?? []);
        };

        $injector
            ->share(UnitOfWorkMap::class)
            ->delegate(UnitOfWorkMap::class, $factory);
    }
}
