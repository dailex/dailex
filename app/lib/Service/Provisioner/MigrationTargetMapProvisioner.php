<?php

namespace Dailex\Service\Provisioner;

use Auryn\Injector;
use Daikon\Config\ConfigProviderInterface;
use Daikon\Dbal\Connector\ConnectorMap;
use Daikon\Dbal\Migration\MigrationAdapterMap;
use Daikon\Dbal\Migration\MigrationTarget;
use Daikon\Dbal\Migration\MigrationTargetMap;
use Dailex\Migration\FilesystemLoader;
use Dailex\Service\ServiceDefinitionInterface;
use Dailex\Service\ServiceLocatorInterface;
use Pimple\Container;

final class MigrationTargetMapProvisioner implements ProvisionerInterface
{
    public function provision(
        Container $app,
        Injector $injector,
        ConfigProviderInterface $configProvider,
        ServiceDefinitionInterface $serviceDefinition
    ): void {
        $serviceClass = $serviceDefinition->getServiceClass();
        $adapterConfigs = $configProvider->get('migrations.migration_adapters', []);
        $targetConfigs = $configProvider->get('migrations.migration_targets', []);

        $this->delegateAdapterMap($injector, $adapterConfigs);
        $this->delegateTargetMap($injector, $targetConfigs);
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
        $factory = function (MigrationAdapterMap $adapterMap) use ($injector, $targetConfigs) {
            $migrationTargets = [];
            foreach ($targetConfigs as $targetName => $targetConfig) {
                $migrationTarget = $injector->make(
                    MigrationTarget::class,
                    [
                        ':name' => $targetName,
                        ':enabled' => $targetConfig['enabled'],
                        ':migrationAdapter' => $adapterMap->get($targetConfig['migration_adapter']),
                        ':migrationLoader' => $injector->make(
                            FilesystemLoader::class, [':location' => $targetConfig['location']]
                        )
                    ]
                );
                $migrationTargets[$targetName] = $migrationTarget;
            }
            return new MigrationTargetMap($migrationTargets);
        };

        $injector->delegate(MigrationTargetMap::class, $factory)->share(MigrationTargetMap::class);
    }
}
