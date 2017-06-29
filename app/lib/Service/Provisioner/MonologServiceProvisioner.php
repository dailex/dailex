<?php

namespace Dailex\Service\Provisioner;

use Auryn\Injector;
use Daikon\Config\ConfigProviderInterface;
use Dailex\Service\ServiceDefinitionInterface;
use Pimple\Container;
use Silex\Provider\MonologServiceProvider;

final class MonologServiceProvisioner implements ProvisionerInterface
{
    public function provision(
        Container $app,
        Injector $injector,
        ConfigProviderInterface $configProvider,
        ServiceDefinitionInterface $serviceDefinition
    ): void {
        $provisionerSettings = $serviceDefinition->getProvisionerSettings();

        $app->register(
            new MonologServiceProvider,
            ['monolog.logfile' => $provisionerSettings['location']]
        );

        $injector
            ->share($app['logger'])
            ->alias($provisionerSettings['alias'], get_class($app['logger']));
    }
}
