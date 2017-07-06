<?php

namespace Dailex\Infrastructure\Projector;

use Daikon\Cqrs\EventStore\CommitStreamId;
use Daikon\DataStructures\TypedMapTrait;

final class ProjectorMap implements \IteratorAggregate, \Countable
{
    use TypedMapTrait;

    public function __construct(array $projectors = [])
    {
        $this->init($projectors, ProjectorInterface::class);
    }

    public function filterByStreamId(CommitStreamId $streamId)
    {
        $prefix = explode('-', $streamId->toNative(), 2)[0];
        return $this->compositeMap->filter(function ($name) use ($prefix) {
            return strpos($name, $prefix) === 0;
        });
    }
}
