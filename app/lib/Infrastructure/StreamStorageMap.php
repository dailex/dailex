<?php

declare(strict_types=1);

namespace Dailex\Infrastructure;

use Daikon\DataStructure\TypedMapTrait;
use Daikon\EventSourcing\EventStore\Storage\StreamStorageInterface;

final class StreamStorageMap implements \IteratorAggregate, \Countable
{
    use TypedMapTrait;

    public function __construct(array $streamStores = [])
    {
        $this->init($streamStores, StreamStorageInterface::class);
    }
}
