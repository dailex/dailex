<?php

namespace Dailex\Console\Command\Worker;

use Daikon\AsyncJob\Worker\WorkerMap;
use Daikon\Config\ConfigProviderInterface;
use Dailex\Console\Command\Command;
use Dailex\Service\ServiceLocatorInterface;

abstract class WorkerCommand extends Command
{
    protected $workerMap;

    public function __construct(ConfigProviderInterface $configProvider, WorkerMap $workerMap)
    {
        parent::__construct($configProvider);
        $this->workerMap = $workerMap;
    }
}
