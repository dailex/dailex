<?php

namespace Dailex\Service\Provisioner;

use Auryn\Injector;
use Daikon\Config\ConfigProviderInterface;
use Dailex\Exception\ConfigException;
use Dailex\Service\ServiceDefinitionInterface;
use Pimple\Container;
use Psr\Log\LoggerInterface;
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
        $settings = $serviceDefinition->getSettings();

        if (!isset($settings['location'])) {
            throw new ConfigException('Please provide a logging service output location.');
        }

        $app->register(
            new MonologServiceProvider,
            ['monolog.logfile' => $settings['location']]
        );

        $loggingService = new $serviceClass($app['logger']);

        $injector
            ->share($loggingService)
            ->alias(LoggerInterface::class, $serviceClass);
    }
}
