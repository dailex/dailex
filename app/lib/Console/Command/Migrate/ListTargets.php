<?php

namespace Dailex\Console\Command\Migrate;

use Dailex\Infrastructure\Migration\MigrationTargetInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class ListTargets extends MigrateCommand
{
    protected function configure()
    {
        $this
            ->setName('migrate:ls')
            ->setDescription('Lists available migration targets.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($this->migrationTargetMap->isEmpty()) {
            $output->writeln('<error>There are no migration targets available.</error>');
            $output->writeln('');
            exit;
        }

        foreach ($this->migrationTargetMap as $targetName => $migrationTarget) {
            $migrationList = $migrationTarget->getMigrationList();
            $executedMigrations = $migrationList->getExecutedMigrations();
            $pendingMigrations = $migrationList->getPendingMigrations();
            $output->writeln($targetName.':');
            $output->writeln('  Enabled: '.($migrationTarget->isEnabled() ? 'true' : 'false'));
            $output->writeln('  Executed Migrations: '.count($executedMigrations));
            $output->writeln('  Pending Migrations: '.count($pendingMigrations));
        }
    }
}
