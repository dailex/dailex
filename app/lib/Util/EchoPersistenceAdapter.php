<?php
namespace Dailex\Util;

use Daikon\Cqrs\EventStore\PersistenceAdapterInterface;
use Daikon\Cqrs\EventStore\CommitStream;
use Daikon\Cqrs\EventStore\CommitStreamId;
use Daikon\Cqrs\EventStore\CommitStreamInterface;
use Daikon\Cqrs\EventStore\CommitStreamRevision;
use Daikon\Cqrs\EventStore\StoreResultInterface;
use Daikon\Cqrs\EventStore\StoreSuccess;

final class EchoPersistenceAdapter implements PersistenceAdapterInterface
{
    public function loadStream(CommitStreamId $streamId, CommitStreamRevision $revision = null): CommitStreamInterface
    {
        echo "<h4>".__METHOD__." streamId: $streamId, revision: $revision</h4>";
        return CommitStream::fromStreamId($streamId);
    }

    public function storeStream(CommitStreamInterface $stream, CommitStreamRevision $storeHead): StoreResultInterface
    {
        echo "<h4>".__METHOD__." streamId: ".$stream->getStreamId().", revision: ".$stream->getStreamRevision()."</h4>";
        return new StoreSuccess;
    }
}
