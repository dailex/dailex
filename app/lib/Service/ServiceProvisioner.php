<?php

namespace Dailex\Service;

use Auryn\Injector;
use Daikon\Config\ConfigProviderInterface;
use Dailex\Exception\ConfigException;
use Dailex\Service\ServiceLocator;
use Dailex\Service\ServiceLocatorInterface;
use Dailex\Service\Provisioner\ProvisionerInterface;
use Pimple\Container;
use Silex\Api\EventListenerProviderInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class ServiceProvisioner implements ServiceProvisionerInterface
{
    private $app;

    private $injector;

    private $configProvider;

    public function __construct(Container $app, Injector $injector, ConfigProviderInterface $configProvider)
    {
        $this->app = $app;
        $this->injector = $injector;
        $this->configProvider = $configProvider;
    }

    public function provision(): ServiceLocatorInterface
    {
        $serviceDefinitionMap = $this->getServiceDefinitionMap();
        $this->makeServices($serviceDefinitionMap);

        $serviceLocatorState = [
            ':injector' => $this->injector,
            ':serviceDefinitionMap' => $serviceDefinitionMap
        ];

        return $this->injector
            ->share(ServiceLocator::class)
            ->alias(ServiceLocatorInterface::class, ServiceLocator::class)
            ->make(ServiceLocator::class, $serviceLocatorState);
    }

    public function subscribe(Container $app, EventDispatcherInterface $dispatcher): void
    {
        $serviceDefinitionMap = $this->getServiceDefinitionMap();
        foreach ($serviceDefinitionMap as $serviceDefinition) {
            $provisionerClass = $serviceDefinition->getProvisionerClass();
            //@todo are we making services here unnecessarily?
            $provisioner = $this->injector->make($provisionerClass);
            if ($provisioner instanceof EventListenerProviderInterface) {
                $provisioner->subscribe($app, $dispatcher);
            }
        }
    }

    private function getServiceDefinitionMap(): ServiceDefinitionMap
    {
        $serviceConfigs = $this->configProvider->get('services::*::*');

        $serviceDefinitions = [];
        foreach ($serviceConfigs as $namespace => $namespaceDefinitions) {
            foreach ($namespaceDefinitions as $rootPath => $rootDefinitions) {
                foreach ($rootDefinitions as $serviceName => $serviceDefinition) {
                    $serviceKey = sprintf('%s.%s.%s', $namespace, $rootPath, $serviceName);
                    $serviceDefinitions[$serviceKey] = new ServiceDefinition(
                        $serviceDefinition['class'],
                        $serviceDefinition['provisioner']['class'] ?? null,
                        $serviceDefinition['provisioner']['settings'] ?? [],
                        $serviceDefinition['subscriptions'] ?? []
                    );
                }
            }
        }

        return new ServiceDefinitionMap($serviceDefinitions);
    }

    private function makeServices(ServiceDefinitionMap $serviceDefinitionMap): void
    {
        foreach ($serviceDefinitionMap->getIterator() as $serviceKey => $serviceDefinition) {
            $provisionerClass = $serviceDefinition->getProvisionerClass();
            $provisioner = $this->injector->make($provisionerClass);
            if ($provisioner instanceof ProvisionerInterface) {
                $provisioner->provision(
                    $this->app,
                    $this->injector,
                    $this->configProvider,
                    $serviceDefinition
                );
            } else {
                throw new ConfigException(
                    sprintf('Provisioner %s must implement %s', $provisionerClass, ProvisionerInterface::class)
                );
            }
        }
    }
}
