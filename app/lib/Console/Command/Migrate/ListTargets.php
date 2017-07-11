<?php

namespace Dailex\Console\Command\Migrate;

use Dailex\Infrastructure\Migration\MigrationTargetInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class ListTargets extends MigrateCommand
{
    protected function configure()
    {
        $this
            ->setName('migrate:ls')
            ->setDescription('Lists available migration targets.')
            ->addOption(
                'target',
                null,
                InputOption::VALUE_REQUIRED,
                'Name of the target to list.'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $target = $input->getOption('target');

        foreach ($this->migrationTargetMap as $targetName => $migrationTarget) {
            if ($target && $target !== $targetName) {
                continue;
            }
            $migrationList = $migrationTarget->getMigrationList();
            $executedMigrations = $migrationList->getExecutedMigrations();
            $pendingMigrations = $migrationList->getPendingMigrations();
            $output->writeln('Summary for migration target <options=bold>'.$targetName.'</>');
            $output->writeln('  Enabled: '.($migrationTarget->isEnabled() ? 'true' : 'false'));
            $output->writeln('  Executed Migrations: '.count($executedMigrations));
            $output->writeln('  Pending Migrations: '.count($pendingMigrations));
        }
    }
}
