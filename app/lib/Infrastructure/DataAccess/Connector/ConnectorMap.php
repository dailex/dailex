<?php

namespace Dailex\Infrastructure\DataAccess\Connector;

use Daikon\DataStructures\TypedMapTrait;

final class ConnectorMap implements \IteratorAggregate, \Countable
{
    use TypedMapTrait;

    public function __construct(array $connectors = [])
    {
        $this->init($connectors, ConnectorInterface::class);
    }
}
