<?php

namespace Dailex\Console\Command\Migrate;

use Daikon\Config\ConfigProviderInterface;
use Daikon\Dbal\Migration\MigrationTargetMap;
use Dailex\Console\Command\Command;

abstract class MigrateCommand extends Command
{
    protected $migrationTargetMap;

    public function __construct(
        ConfigProviderInterface $configProvider,
        MigrationTargetMap $migrationTargetMap
    ) {
        parent::__construct($configProvider);

        $this->migrationTargetMap = $migrationTargetMap;
    }
}
