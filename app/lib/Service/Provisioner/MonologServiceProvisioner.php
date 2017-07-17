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
        $serviceClass = $serviceDefinition->getServiceClass();
        $provisionerSettings = $serviceDefinition->getProvisionerSettings();

        $app->register(
            new MonologServiceProvider,
            ['monolog.logfile' => $provisionerSettings['location']]
        );

        $loggingService = new $serviceClass($app['logger']);

        $injector
            ->share($loggingService)
            ->alias($provisionerSettings['alias'], $serviceClass);
    }
}
