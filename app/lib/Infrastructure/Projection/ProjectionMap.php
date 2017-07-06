<?php

namespace Dailex\Infrastructure\Projection;

use Daikon\DataStructures\TypedMapTrait;

final class ProjectionMap implements \IteratorAggregate, \Countable
{
    use TypedMapTrait;

    public function init(array $projections = [])
    {
        $this->init($projections, ProjectionInterface::class);
    }
}
