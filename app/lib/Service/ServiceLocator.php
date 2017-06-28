<?php

namespace Dailex\Service;

use Auryn\Injector;
use Dailex\Exception\RuntimeException;

final class ServiceLocator implements ServiceLocatorInterface
{
    private $injector;

    private $serviceMap;

    public function __construct(Injector $injector, ServiceDefinitionMap $serviceMap)
    {
        $this->injector = $injector;
        $this->serviceMap = $serviceMap;
    }

    public function get($id)
    {
        if (!$this->serviceMap->hasKey($id)) {
            throw new RuntimeException(sprintf('No service found for given service id: "%s".', $id));
        }
        $serviceDefinition = $this->serviceMap->getItem($id);
        return $this->injector->make($serviceDefinition->getClass());
    }

    public function has($id)
    {
        return $this->serviceMap->hasKey($id);
    }

    public function __call($method, array $args)
    {
        if (preg_match('/^get(\w+)$/', $method, $matches)) {
            $id = "dailex.infrastructure.".StringToolkit::asSnakeCase($matches[1]);
            return $this->get($id);
        }
    }

    public function make($implementor, array $state = [])
    {
        return $this->injector->make($implementor, $state);
    }
}
