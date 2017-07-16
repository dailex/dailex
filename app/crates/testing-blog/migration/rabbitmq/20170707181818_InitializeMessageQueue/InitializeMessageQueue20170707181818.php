<?php

namespace Testing\Blog\Migration\RabbitMq;

use Daikon\Dbal\Migration\MigrationInterface;
use Daikon\RabbitMq3\Migration\RabbitMq3MigrationTrait;

final class InitializeMessageQueue20170707181818 implements MigrationInterface
{
    use RabbitMq3MigrationTrait;

    public function getDescription(string $direction = self::MIGRATE_UP): string
    {
        return $direction === self::MIGRATE_UP
            ? 'Create a RabbitMQ message pipeline for the Testing-Blog context.'
            : 'Delete the RabbitMQ message pipeline for the Testing-Blog context.';
    }

    public function isReversible(): bool
    {
        return true;
    }

    private function up(): void
    {
        $this->createMigrationList('testing.blog.migration_list');
        $this->createMessagePipeline('testing.blog.messages');
        $this->bindExchange('amq.topic', 'testing.blog.messages', 'testing.blog.#');
    }

    private function down(): void
    {
        $this->deleteMessagePipeline('testing.blog.messages');
        $this->deleteExchange('testing.blog.migration_list');
    }
}
