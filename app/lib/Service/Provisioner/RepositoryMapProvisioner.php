<?php

namespace Dailex\Service\Provisioner;

use Auryn\Injector;
use Daikon\Config\ConfigProviderInterface;
use Daikon\Dbal\Storage\StorageAdapterMap;
use Dailex\Service\ServiceDefinitionInterface;
use Pimple\Container;

final class RepositoryMapProvisioner implements ProvisionerInterface
{
    public function provision(
        Container $app,
        Injector $injector,
        ConfigProviderInterface $configProvider,
        ServiceDefinitionInterface $serviceDefinition
    ): void {
        $serviceClass = $serviceDefinition->getServiceClass();
        $repositoryConfigs = $configProvider->get('databases.repositories', []);

        $factory = function (StorageAdapterMap $storageAdapterMap) use ($injector, $repositoryConfigs, $serviceClass) {
            $repositories = [];
            foreach ($repositoryConfigs as $repositoryName => $repositoryConfig) {
                $repositoryClass = $repositoryConfig['class'];
                $dependencies = [':storageAdapter' => $storageAdapterMap->get($repositoryConfig['storage_adapter'])];
                if (isset($repositoryConfig['search_adapter'])) {
                    $dependencies[':searchAdapter'] = $storageAdapterMap->get($repositoryConfig['search_adapter']);
                }
                $repositories[$repositoryName] = $injector->make($repositoryClass, $dependencies);
            }
            return new $serviceClass($repositories);
        };

        $injector
            ->share($serviceClass)
            ->delegate($serviceClass, $factory);
    }
}
