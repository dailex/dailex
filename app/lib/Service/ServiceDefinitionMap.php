<?php

namespace Dailex\Service;

use Daikon\DataStructure\TypedMapTrait;

final class ServiceDefinitionMap implements \IteratorAggregate, \Countable
{
    use TypedMapTrait;

    public function __construct(array $serviceDefinitions = [])
    {
        $this->init($serviceDefinitions, ServiceDefinitionInterface::class);
    }
}
