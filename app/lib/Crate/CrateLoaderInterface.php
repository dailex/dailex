<?php

namespace Dailex\Crate;

use Dailex\Crate\CrateManifestMap;
use Silex\Application;

interface CrateLoaderInterface
{
    public function loadCrates(Application $app, CrateManifestMap $crateManifestMap);
}
