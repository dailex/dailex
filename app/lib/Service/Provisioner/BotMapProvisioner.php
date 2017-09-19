<?php

namespace Dailex\Service\Provisioner;

use Auryn\Injector;
use Daikon\BotMan\BotMap;
use Daikon\Config\ConfigProviderInterface;
use Dailex\Service\ServiceDefinitionInterface;
use Pimple\Container;

final class BotMapProvisioner implements ProvisionerInterface
{
    public function provision(
        Container $app,
        Injector $injector,
        ConfigProviderInterface $configProvider,
        ServiceDefinitionInterface $serviceDefinition
    ): void {
        $botConfigs = $configProvider->get('bots', []);
        $factory = function () use ($injector, $botConfigs) {
            $bots = [];
            foreach ($botConfigs as $botName => $botConfig) {
                $bots[$botName] = $injector->make(
                    $botConfig['class'],
                    [':settings' => $botConfig['settings'] ?? []]
                );
            }
            return new BotMap($bots);
        };

        $injector
            ->share(BotMap::class)
            ->delegate(BotMap::class, $factory);
    }
}
