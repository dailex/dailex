<?php

namespace Dailex\Bootstrap;

use Silex\Application;

interface BootstrapInterface
{
    public function __invoke(Application $app): void;
}
