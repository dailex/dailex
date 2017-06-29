<?php

namespace Dailex\Service;

use Auryn\Injector;
use Daikon\Config\ConfigProviderInterface;
use Daikon\Entity\EntityType\EntityTypeMap;
use Dailex\Exception\ConfigException;
use Dailex\Service\ServiceLocator;
use Dailex\Service\ServiceLocatorInterface;
use Dailex\Service\Provisioner\DefaultProvisioner;
use Dailex\Service\Provisioner\ProvisionerInterface;
use Pimple\Container;
use Silex\Api\EventListenerProviderInterface;
use SplFileInfo;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class ServiceProvisioner implements ServiceProvisionerInterface
{
    private static $defaultProvisionerClass = DefaultProvisioner::class;

    private $app;

    private $configProvider;

    private $injector;

    public function __construct(
        Container $app,
        ConfigProviderInterface $configProvider,
        Injector $injector
    ) {
        $this->app = $app;
        $this->configProvider = $configProvider;
        $this->injector = $injector;
    }

    public function provision()
    {
        $serviceDefinitionMap = $this->getServiceDefinitionMap();
//         $this->registerEntityTypeMaps();
        $this->evaluateServiceDefinitions($serviceDefinitionMap);

        $serviceLocatorState = [
            ':injector' => $this->injector,
            ':serviceDefinitionMap' => $serviceDefinitionMap
        ];

        return $this->injector
            ->share(ServiceLocator::class)
            ->alias(ServiceLocatorInterface::class, ServiceLocator::class)
            ->make(ServiceLocator::class, $serviceLocatorState);
    }

    public function subscribe(Container $app, EventDispatcherInterface $dispatcher)
    {
        $serviceDefinitionMap = $this->getServiceDefinitionMap();
        foreach ($serviceDefinitionMap as $serviceDefinition) {
            if ($serviceDefinition->hasProvisioner()) {
                $provisionerConfig = $serviceDefinition->getProvisioner();
                $provisioner = $this->injector->make($provisionerConfig['class']);
                if ($provisioner instanceof EventListenerProviderInterface) {
                    $provisioner->subscribe($app, $dispatcher);
                }
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
                    $serviceDefinition['name'] = $serviceKey;
                    if (isset($serviceDefinition['provisioner'])) {
                        if (!isset($serviceDefinition['provisioner']['class'])) {
                            $serviceDefinition['provisioner']['class'] = DefaultProvisioner::class;
                        }
                    }
                    $serviceDefinitions[$serviceKey] = new ServiceDefinition($serviceDefinition);
                }
            }
        }

        return new ServiceDefinitionMap($serviceDefinitions);
    }

    private function evaluateServiceDefinitions(ServiceDefinitionMap $serviceDefinitions)
    {
        $defaultProvisioner = $this->injector->make(static::$defaultProvisionerClass);
        foreach ($serviceDefinitions->getIterator() as $serviceKey => $serviceDefinition) {
            if ($serviceDefinition->hasProvisioner()) {
                $this->runServiceProvisioner($serviceDefinition);
            } else {
                $defaultProvisioner->provision(
                    $this->app,
                    $this->injector,
                    $this->configProvider,
                    $serviceDefinition
                );
            }
        }
    }

    private function runServiceProvisioner(ServiceDefinitionInterface $serviceDefinition, array $settings = [])
    {
        $provisionerConfig = $serviceDefinition->getProvisioner();
        $provisionerClass = $provisionerConfig['class'];

        if (!class_exists($provisionerClass)) {
            throw new ConfigException('Unable to load provisioner class: ' . $provisionerClass);
        }

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
                sprintf('Provisioner %s must implement %s', $provisionerClass, ProvisionerInterface::CLASS)
            );
        }
    }

    private function registerEntityTypeMaps()
    {
        $aggregateRootTypes = [];

        foreach ($this->configProvider->getCrateMap() as $crate) {
            foreach (glob($crate->getConfigDir().'/*/entity_schema/aggregate_root.xml') as $schemaFile) {
                $aggregateRootType = $this->loadEntityType($crate->getConfigDir(), $schemaFile);
                $aggregateRootTypes[$aggregateRootType->getPrefix()] = $aggregateRootType;
            }
        }

        $this->injector->share(new EntityTypeMap($aggregateRootTypes));

        foreach ($aggregateRootTypes as $aggregateRootType) {
            $this->injector->share($aggregateRootType);
        }
    }

    private function loadEntityType($crateConfigDir, $schemaFile)
    {
        $schemaFile = new SplFileInfo($schemaFile);
        $iniParser = new ConfigIniParser;
        $config = $iniParser->parse(sprintf('%s/%s.ini', $schemaFile->getPath(), $schemaFile->getBasename('.xml')));
        $schema = (new EntityTypeSchemaXmlParser)->parse($schemaFile->getRealPath());
        $entityType = $schema->getEntityTypeDefinition();

        $class = sprintf('%s\\%s%s', $schema->getNamespace(), $entityType->getName(), $config->getTypeSuffix('Type'));

        return new $class;
    }
}
