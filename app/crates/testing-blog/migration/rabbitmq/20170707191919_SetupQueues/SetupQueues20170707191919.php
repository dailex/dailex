<?php

namespace Testing\Blog\Migration\RabbitMq;

use Daikon\Dbal\Migration\MigrationInterface;
use Daikon\RabbitMq3\Migration\RabbitMq3MigrationTrait;

final class SetupQueues20170707191919 implements MigrationInterface
{
    use RabbitMq3MigrationTrait;

    public function getDescription(string $direction = self::MIGRATE_UP): string
    {
        return $direction === self::MIGRATE_UP
            ? 'Create RabbitMQ message queues for Article resource events.'
            : 'Delete RabbitMQ message queues for Article resource events.';
    }

    public function isReversible(): bool
    {
        return true;
    }

    private function up(): void
    {
        $this->declareQueue('testing.blog.article.messages', false, true, false, false);
        $this->bindQueue('testing.blog.article.messages', 'testing.blog.messages', 'testing.blog.article_routing');
    }

    private function down(): void
    {
        $this->deleteQueue('testing.blog.article.messages');
    }
}
