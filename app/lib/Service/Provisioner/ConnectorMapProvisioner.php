<?php

namespace Dailex\Service\Provisioner;

use Auryn\Injector;
use Daikon\Config\ConfigProviderInterface;
use Daikon\Dbal\Connector\ConnectorMap;
use Dailex\Exception\RuntimeException;
use Dailex\Service\ServiceDefinitionInterface;
use Pimple\Container;

final class ConnectorMapProvisioner implements ProvisionerInterface
{
    public function provision(
        Container $app,
        Injector $injector,
        ConfigProviderInterface $configProvider,
        ServiceDefinitionInterface $serviceDefinition
    ): void {
        $serviceClass = $serviceDefinition->getServiceClass();
        $connectorConfigs = $configProvider->get('databases.connectors');

        $factory = function () use ($injector, $connectorConfigs, $serviceClass) {
            $connectors = [];
            foreach ($connectorConfigs as $connectorName => $connectorConfig) {
                $connectorClass = $connectorConfig['class'];
                $connectors[$connectorName] = $injector->make(
                    $connectorClass,
                    [':settings' => $connectorConfig['settings']]
                );
            }
            return new $serviceClass($connectors);
        };

        $injector
            ->share($serviceClass)
            ->delegate($serviceClass, $factory);
    }
}
