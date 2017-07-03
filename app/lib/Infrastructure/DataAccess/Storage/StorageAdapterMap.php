<?php

namespace Dailex\Infrastructure\DataAccess\Storage;

use Daikon\DataStructures\TypedMapTrait;

final class StorageAdapterMap implements \IteratorAggregate, \Countable
{
    use TypedMapTrait;

    public function __construct(array $streamAdapters = [])
    {
        $this->init($streamAdapters, StorageAdapterInterface::class);
    }
}
