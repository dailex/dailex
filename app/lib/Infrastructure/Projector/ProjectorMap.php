<?php

namespace Dailex\Infrastructure\Projector;

use Daikon\Cqrs\Aggregate\AggregatePrefix;
use Daikon\DataStructures\TypedMapTrait;

final class ProjectorMap implements \IteratorAggregate, \Countable
{
    use TypedMapTrait;

    public function __construct(array $projectors = [])
    {
        $this->init($projectors, ProjectorInterface::class);
    }

    public function filterByAggregatePrefix(AggregatePrefix $aggregatePrefix)
    {
        $prefix = $aggregatePrefix->toNative();
        return $this->compositeMap->filter(function ($key) use ($prefix) {
            return strpos($key, $prefix) === 0;
        });
    }
}
