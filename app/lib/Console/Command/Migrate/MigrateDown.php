<?php

namespace Dailex\Console\Command\Migrate;

use Assert\InvalidArgumentException;
use Daikon\Dbal\Migration\MigrationInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class MigrateDown extends MigrateCommand
{
    protected function configure()
    {
        $this
            ->setName('migrate:down')
            ->setDescription('Migrate down to a specified migration version.')
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
                'The version to migrate towards (if omitted all previous migrations will be reversed if possible).'
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
            $output->writeln(sprintf('Reversing migrations for target <options=bold>%s</>', $targetName));
            try {
                $reversedMigrations = $migrationTarget->migrate(MigrationInterface::MIGRATE_DOWN, $version);
                if ($reversedMigrations->count() > 0) {
                    foreach ($reversedMigrations as $migration) {
                        $output->writeln(sprintf(
                            '  <info>Reversed migration version %d (%s)</info>',
                            $migration->getVersion(),
                            $migration->getName()
                        ));
                    }
                } else {
                    $output->writeln('  <comment>No reversible migrations found</comment>');
                }
            } catch (InvalidArgumentException $exception) {
                $output->writeln('  <error>'.$exception->getMessage().'</error>');
            }
        }
    }
}
