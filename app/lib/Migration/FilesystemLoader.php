<?php

namespace Dailex\Migration;

use Daikon\Dbal\Exception\MigrationException;
use Daikon\Dbal\Migration\MigrationList;
use Daikon\Dbal\Migration\MigrationLoaderInterface;
use Symfony\Component\Finder\Finder;

final class FilesystemLoader implements MigrationLoaderInterface
{
    private $location;

    private $fileFinder;

    public function __construct(string $location, Finder $fileFinder = null)
    {
        $this->location = $location;
        $this->fileFinder = $fileFinder ?: new Finder;
    }

    public function load(): MigrationList
    {
        if (!is_dir($this->location) || !is_readable($this->location)) {
            throw new MigrationException(
                sprintf('Migrations location %s is not a readable directory.', $this->location)
            );
        }

        $migrations = [];
        $migrationFiles = $this->fileFinder->create()->files()->name('*.php')->in($this->location)->sortByName();
        foreach ($migrationFiles as $migrationFile) {
            $declaredClasses = get_declared_classes();
            require_once (string)$migrationFile;
            $migrationClass = current(array_diff(get_declared_classes(), $declaredClasses));
            /*
             * Explicitly not using a service locator to make migration classes here because
             * it could enable unexpected behaviour.
             */
            $migrations[] = new $migrationClass;
        }

        return new MigrationList($migrations);
    }
}
