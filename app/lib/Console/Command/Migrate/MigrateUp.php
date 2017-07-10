<?php

namespace Dailex\Console\Command\Migrate;

use Daikon\Dbal\Migration\MigrationInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class MigrateUp extends MigrateCommand
{
    protected function configure()
    {
        $this
            ->setName('migrate:up')
            ->setDescription('Migrate up to a specified migration version.')
            ->addOption(
                'target',
                null,
                InputOption::VALUE_REQUIRED,
                'Name of the target to migrate (if omitted all targets will be migrated).'
            )
            ->addOption(
                'to',
                null,
                InputOption::VALUE_REQUIRED,
                'The version to migrate towards (if omitted all pendings migrations will be executed).'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $target = $input->getOption('target');
        $version = intval($input->getOption('to'));

        foreach ($this->migrationTargetMap->getEnabledTargets() as $targetName => $migrationTarget) {
            if ($target && $target !== $targetName) {
                continue;
            }
            $output->writeln(sprintf('Executing migrations for target <options=bold>%s</>', $targetName));
            $executedMigrations = $migrationTarget->migrate(MigrationInterface::MIGRATE_UP, $version);
            if ($executedMigrations->count() > 0) {
                foreach ($executedMigrations as $migration) {
                    $output->writeln(sprintf(
                        '  <info>Executed migration version %d (%s)</info>',
                        $migration->getVersion(),
                        $migration->getName()
                    ));
                }
            } else {
                $output->writeln('  <comment>No pending migrations found</comment>');
            }
        }
    }
}
