<?php

namespace Testing\Blog\Article\Repository\Standard;

use Daikon\Dbal\Projection\ProjectionInterface;
use Daikon\Dbal\Projection\ProjectionMap;
use Daikon\Dbal\Repository\RepositoryInterface;
use Daikon\Dbal\Storage\StorageAdapterInterface;

final class ArticleRepository implements RepositoryInterface
{
    private $storageAdapter;

    public function __construct(StorageAdapterInterface $storageAdapter)
    {
        $this->storageAdapter = $storageAdapter;
    }

    public function findById(string $identifier): ProjectionInterface
    {
        return $this->storageAdapter->read($identifier);
    }

    public function findByIds(array $identifiers): ProjectionMap
    {
    }

    public function persist(ProjectionInterface $projection): bool
    {
        return $this->storageAdapter->write($projection->getAggregateId(), $projection->toArray());
    }

    public function makeProjection(): ProjectionInterface
    {
        return Article::fromArray([
            '@type' => Article::class,
            '@parent' => null
        ]);
    }
}
