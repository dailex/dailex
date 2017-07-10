<?php

namespace Testing\Blog\Migration\CouchDb;

use Daikon\CouchDb\Migration\CouchDbMigrationTrait;
use Daikon\Dbal\Migration\MigrationInterface;

/*
 * The database name that is to be migrated is defined in the connectors configuration
 * file for the "testing.blog" context.
 */
final class InitializeEventStore20170707181818 implements MigrationInterface
{
    use CouchDbMigrationTrait;

    public function getDescription(string $direction = self::MIGRATE_UP): string
    {
        return $direction === self::MIGRATE_UP
            ? 'Create the CouchDb database for the "testing.blog" context.'
            : 'Delete the CouchDb database for the "testing.blog" context.';
    }

    public function isReversible(): bool
    {
        return true;
    }

    private function up(): void
    {
        $this->createDatabase();
    }

    private function down(): void
    {
        $this->deleteDatabase();
    }
}
