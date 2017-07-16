<?php

namespace Dailex\Console\Command\Worker;

use Daikon\Config\ConfigProviderInterface;
use Daikon\Dbal\Connector\ConnectorMap;
use Dailex\Console\Command\Command;
use Dailex\Service\ServiceLocatorInterface;

abstract class WorkerCommand extends Command
{
    protected $serviceLocator;

    protected $connectorMap;

    public function __construct(
        ConfigProviderInterface $configProvider,
        ServiceLocatorInterface $serviceLocator,
        ConnectorMap $connectorMap
    ) {
        parent::__construct($configProvider);

        $this->serviceLocator = $serviceLocator;
        $this->connectorMap = $connectorMap;
    }
}
