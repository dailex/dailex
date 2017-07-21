<?php

namespace Dailex\Service\Provisioner;

use Auryn\Injector;
use Daikon\Config\ConfigProviderInterface;
use Daikon\Dbal\Connector\ConnectorMap;
use Daikon\Dbal\Migration\MigrationAdapterMap;
use Daikon\Dbal\Migration\MigrationLoaderMap;
use Daikon\Dbal\Migration\MigrationTarget;
use Daikon\Dbal\Migration\MigrationTargetMap;
use Dailex\Service\ServiceDefinitionInterface;
use Pimple\Container;

final class MigrationTargetMapProvisioner implements ProvisionerInterface
{
    public function provision(
        Container $app,
        Injector $injector,
        ConfigProviderInterface $configProvider,
        ServiceDefinitionInterface $serviceDefinition
    ): void {
        $loaderConfigs = $configProvider->get('migrations.migration_loaders', []);
        $adapterConfigs = $configProvider->get('migrations.migration_adapters', []);
        $targetConfigs = $configProvider->get('migrations.migration_targets', []);

        $this->delegateLoaderMap($injector, $loaderConfigs);
        $this->delegateAdapterMap($injector, $adapterConfigs);
        $this->delegateTargetMap($injector, $targetConfigs);
    }

    private function delegateLoaderMap(Injector $injector, array $loaderConfigs)
    {
        $factory = function (ConnectorMap $connectorMap) use ($injector, $loaderConfigs) {
            $migrationLoaders = [];
            foreach ($loaderConfigs as $loaderName => $loaderConfig) {
                $migrationLoader = $injector->make(
                    $loaderConfig['class'],
                    [
                        ':connector' => $connectorMap->get($loaderConfig['connector']),
                        ':settings' => $loaderConfig['settings'] ?? []
                    ]
                );
                $migrationLoaders[$loaderName] = $migrationLoader;
            }
            return new MigrationLoaderMap($migrationLoaders);
        };

        $injector->delegate(MigrationLoaderMap::class, $factory)->share(MigrationLoaderMap::class);
    }

    private function delegateAdapterMap(Injector $injector, array $adapterConfigs)
    {
        $factory = function (ConnectorMap $connectorMap) use ($injector, $adapterConfigs) {
            $migrationAdapters = [];
            foreach ($adapterConfigs as $adapterName => $adapterConfig) {
                $migrationAdapter = $injector->make(
                    $adapterConfig['class'],
                    [
                        ':connector' => $connectorMap->get($adapterConfig['connector']),
                        ':settings' => $adapterConfig['settings'] ?? []
                    ]
                );
                $migrationAdapters[$adapterName] = $migrationAdapter;
            }
            return new MigrationAdapterMap($migrationAdapters);
        };

        $injector->delegate(MigrationAdapterMap::class, $factory)->share(MigrationAdapterMap::class);
    }

    private function delegateTargetMap(Injector $injector, array $targetConfigs)
    {
        $factory = function (
            MigrationAdapterMap $adapterMap,
            MigrationLoaderMap $loaderMap
        ) use (
            $injector,
            $targetConfigs
        ) {
            $migrationTargets = [];
            foreach ($targetConfigs as $targetName => $targetConfig) {
                $migrationTarget = $injector->make(
                    MigrationTarget::class,
                    [
                        ':name' => $targetName,
                        ':enabled' => $targetConfig['enabled'],
                        ':migrationAdapter' => $adapterMap->get($targetConfig['migration_adapter']),
                        ':migrationLoader' => $loaderMap->get($targetConfig['migration_loader'])
                    ]
                );
                $migrationTargets[$targetName] = $migrationTarget;
            }
            return new MigrationTargetMap($migrationTargets);
        };

        $injector->delegate(MigrationTargetMap::class, $factory)->share(MigrationTargetMap::class);
    }
}
