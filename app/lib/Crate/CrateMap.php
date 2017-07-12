<?php

namespace Dailex\Crate;

use Daikon\DataStructure\TypedMapTrait;

final class CrateMap implements \IteratorAggregate, \Countable
{
    use TypedMapTrait;

    public function __construct(array $crates = [])
    {
        $this->init($crates, CrateInterface::class);
    }

    public function getLocations(): array
    {
        $locations = [];
        foreach ($this->compositeMap as $crate) {
            $locations[] = $crate->getLocation();
        }
        return $locations;
    }
}
