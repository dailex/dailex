<?php

namespace Dailex\Crate;

use Daikon\DataStructures\TypedMapTrait;

class CrateMap
{
    use TypedMapTrait;

    public function getItemImplementor()
    {
        return CrateInterface::CLASS;
    }
}
