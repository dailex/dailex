<?php

namespace Dailex\Service\Provisioner;

use Auryn\Injector;
use Daikon\Config\ConfigProviderInterface;
use Daikon\ReadModel\Repository\RepositoryMap;
use Daikon\ReadModel\Projector\EventProjector;
use Dailex\Service\ServiceDefinitionInterface;
use Pimple\Container;

final class EventProjectorMapProvisioner implements ProvisionerInterface
{
    public function provision(
        Container $app,
        Injector $injector,
        ConfigProviderInterface $configProvider,
        ServiceDefinitionInterface $serviceDefinition
    ): void {
        $serviceClass = $serviceDefinition->getServiceClass();
        $projectorConfigs = $configProvider->get('databases.projectors', []);

        $factory = function (RepositoryMap $repositoryMap) use ($injector, $projectorConfigs, $serviceClass) {
            $projectors = [];
            foreach ($projectorConfigs as $projectorName => $projectorConfig) {
                $projectorClass = $projectorConfig['class'];
                $projectorEvents = $projectorConfig['events'];
                $projectors[$projectorName] = new EventProjector(
                    $projectorEvents,
                    $injector->make(
                        $projectorClass,
                        [ ':repository' => $repositoryMap->get($projectorConfig['repository']) ]
                    )
                );
            }
            return new $serviceClass($projectors);
        };

        $injector
            ->share($serviceClass)
            ->delegate($serviceClass, $factory);
    }
}
