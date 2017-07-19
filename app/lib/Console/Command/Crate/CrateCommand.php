<?php

namespace Dailex\Console\Command\Crate;

use Daikon\Config\ConfigProviderInterface;
use Dailex\Console\Command\Command;
use Dailex\Crate\CrateMap;

abstract class CrateCommand extends Command
{
    protected $crateMap;

    public function __construct(ConfigProviderInterface $configProvider, CrateMap $crateMap)
    {
        parent::__construct($configProvider);
        $this->crateMap = $crateMap;
    }
}
