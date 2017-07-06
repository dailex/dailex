<?php

namespace Dailex\Service\Provisioner;

use Auryn\Injector;
use Daikon\Config\ConfigProviderInterface;
use Daikon\Dbal\Repository\RepositoryMap;
use Dailex\Service\ServiceDefinitionInterface;
use Pimple\Container;

final class ProjectorMapProvisioner implements ProvisionerInterface
{
    public function provision(
        Container $app,
        Injector $injector,
        ConfigProviderInterface $configProvider,
        ServiceDefinitionInterface $serviceDefinition
    ): void {
        $serviceClass = $serviceDefinition->getServiceClass();
        $projectorConfigs = $configProvider->get('databases.projectors');

        $factory = function (RepositoryMap $repositoryMap) use ($injector, $projectorConfigs, $serviceClass) {
            $projectors = [];
            foreach ($projectorConfigs as $projectorName => $projectorConfig) {
                $projectorClass = $projectorConfig['class'];
                $projectors[$projectorName] = $injector->make(
                    $projectorClass,
                    [':repository' => $repositoryMap->get($projectorConfig['repository'])]
                );
            }
            return new $serviceClass($projectors);
        };

        $injector
            ->share($serviceClass)
            ->delegate($serviceClass, $factory);
    }
}
