<?php

declare(strict_types=1);

namespace Dailex\Infrastructure;

use Daikon\DataStructure\TypedMapTrait;
use Daikon\EventSourcing\EventStore\UnitOfWorkInterface;

final class UnitOfWorkMap implements \IteratorAggregate, \Countable
{
    use TypedMapTrait;

    public function __construct(array $unitsOfWork = [])
    {
        $this->init($unitsOfWork, UnitOfWorkInterface::class);
    }
}
