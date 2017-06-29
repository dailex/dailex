<?php

namespace Dailex\Service;

use Auryn\Injector;
use Dailex\Exception\RuntimeException;
use Dailex\Util\StringToolkit;

final class ServiceLocator implements ServiceLocatorInterface
{
    private $injector;

    private $serviceDefinitionMap;

    public function __construct(Injector $injector, ServiceDefinitionMap $serviceDefinitionMap)
    {
        $this->injector = $injector;
        $this->serviceDefinitionMap = $serviceDefinitionMap;
    }

    public function get($id)
    {
        if (!$this->serviceDefinitionMap->has($id)) {
            throw new RuntimeException(sprintf('No service found for given service id: "%s".', $id));
        }
        $serviceDefinition = $this->serviceDefinitionMap->get($id);
        return $this->injector->make($serviceDefinition->getClass());
    }

    public function has($id)
    {
        return $this->serviceDefinitionMap->has($id);
    }

    public function __call($method, array $args)
    {
        if (preg_match('/^get(\w+)$/', $method, $matches)) {
            $id = 'dailex.infrastructure.'.StringToolkit::asSnakeCase($matches[1]);
            return $this->get($id);
        }
    }

    public function make($implementor, array $state = [])
    {
        return $this->injector->make($implementor, $state);
    }
}
