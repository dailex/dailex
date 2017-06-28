<?php

namespace Dailex\Crate;

use Dailex\Crate\CrateManifestInterface;
use Daikon\DataStructures\TypedMapTrait;

class CrateManifestMap
{
    use TypedMapTrait;

    public function getItemImplementor()
    {
        return CrateManifestInterface::CLASS;
    }
}
