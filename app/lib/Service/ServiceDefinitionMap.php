<?php

namespace Dailex\Service;

use Daikon\DataStructures\TypedMapTrait;

final class ServiceDefinitionMap implements \IteratorAggregate, \Countable
{
    use TypedMapTrait;

    public function __construct(array $serviceDefinitions = [])
    {
        // @todo stricter init
        $this->init($serviceDefinitions, ServiceDefinitionInterface::class);
    }
}
