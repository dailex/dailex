<?php

namespace Dailex\Infrastructure\DataAccess\CouchDb2;

use Daikon\Cqrs\EventStore\CommitStreamId;
use Daikon\Cqrs\EventStore\CommitStreamInterface;
use Daikon\Cqrs\EventStore\CommitStreamRevision;
use Daikon\Cqrs\EventStore\PersistenceAdapterInterface;
use Daikon\Cqrs\EventStore\StoreResultInterface;
use Daikon\Cqrs\EventStore\StoreSuccess;
use Dailex\Infrastructure\DataAccess\CouchDb2\Connector\CouchDb2Connector;

final class CouchDb2PersistenceAdapter implements PersistenceAdapterInterface
{
    private $connector;

    public function __construct(CouchDb2Connector $connector)
    {
        $this->connector = $connector;
    }

    public function loadStream(CommitStreamId $streamId, CommitStreamRevision $revision = null): CommitStreamInterface
    {

    }

    public function storeStream(CommitStreamInterface $stream, CommitStreamRevision $storeHead): StoreResultInterface
    {
        $client = $this->connector->getConnection();
        return new StoreSuccess;
    }
}
