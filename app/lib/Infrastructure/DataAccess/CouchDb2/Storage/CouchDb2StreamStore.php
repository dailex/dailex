<?php

namespace Dailex\Infrastructure\DataAccess\CouchDb2\Storage;

use Daikon\Cqrs\EventStore\CommitStream;
use Daikon\Cqrs\EventStore\CommitStreamId;
use Daikon\Cqrs\EventStore\CommitStreamInterface;
use Daikon\Cqrs\EventStore\CommitStreamRevision;
use Daikon\Cqrs\EventStore\StoreResultInterface;
use Daikon\Cqrs\EventStore\StoreSuccess;
use Daikon\Cqrs\EventStore\StreamStoreInterface;
use Dailex\Infrastructure\DataAccess\CouchDb2\Storage\CouchDb2StorageAdapter;

final class CouchDb2StreamStore implements StreamStoreInterface
{
    private $storageAdapter;

    public function __construct(CouchDb2StorageAdapter $storageAdapter)
    {
        $this->storageAdapter = $storageAdapter;
    }

    public function checkout(
        CommitStreamId $streamId,
        CommitStreamRevision $from = null,
        CommitStreamRevision $to = null
    ): CommitStreamInterface {
        $commitSequence = $this->storageAdapter->read($streamId->toNative());
        return new CommitStream($streamId, $commitSequence);
    }

    public function commit(CommitStreamInterface $stream, CommitStreamRevision $storeHead): StoreResultInterface
    {
        $commitSequence = $stream->getCommitRange($storeHead, $stream->getStreamRevision());
        foreach ($commitSequence as $commit) {
            $identifier = $stream->getStreamId()->toNative().'-'.$commit->getStreamRevision();
            $this->storageAdapter->write($identifier, $commit->toArray());
        }
        return new StoreSuccess;
    }
}
