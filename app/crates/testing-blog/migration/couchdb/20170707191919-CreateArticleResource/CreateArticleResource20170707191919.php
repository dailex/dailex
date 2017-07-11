<?php

namespace Testing\Blog\Migration\CouchDb;

use Daikon\CouchDb\Migration\CouchDbMigrationTrait;
use Daikon\Dbal\Migration\MigrationInterface;

final class CreateArticleResource20170707191919 implements MigrationInterface
{
    use CouchDbMigrationTrait;

    public function getDescription(string $direction = self::MIGRATE_UP): string
    {
        return $direction === self::MIGRATE_UP
            ? 'Create Article resource views for the Testing\Blog context.'
            : 'Delete Article resource views for the Testing\Blog context.';
    }

    public function isReversible(): bool
    {
        return true;
    }

    private function up(): void
    {
        $this->createDesignDoc(
            'testing-blog-article',
            [
                'commit_stream' => [
                    'map' => $this->loadFile('commit_stream.map.js'),
                    'reduce' => $this->loadFile('commit_stream.reduce.js')
                ],
                'commits_by_timestamp' => [
                    'map' => $this->loadFile('commits_by_timestamp.map.js')
                ]
            ]
        );
    }

    private function down(): void
    {
        $this->deleteDesignDoc('testing-blog-article');
    }

    private function loadFile(string $filename): string
    {
        return file_get_contents(sprintf('%s/%s', __DIR__, $filename));
    }
}
