<?php

use Daikon\Cqrs\Aggregate\Revision;
use Daikon\Cqrs\EventStore\CommitStream;
use Daikon\Cqrs\EventStore\CommitStreamInterface;
use Daikon\Cqrs\EventStore\PersistenceAdapterInterface;
use Daikon\Cqrs\EventStore\StreamId;
use Daikon\Cqrs\EventStore\UnitOfWork;
use Daikon\Cqrs\Projection\StandardProjector;
use Daikon\MessageBus\Channel\Channel;
use Daikon\MessageBus\Channel\ChannelMap;
use Daikon\MessageBus\Channel\Subscription\MessageHandler\MessageHandlerList;
use Daikon\MessageBus\Channel\Subscription\Subscription;
use Daikon\MessageBus\Channel\Subscription\SubscriptionMap;
use Daikon\MessageBus\Channel\Subscription\Transport\InProcessTransport;
use Daikon\MessageBus\EnvelopeInterface;
use Daikon\MessageBus\MessageBus;
use Daikon\Tests\Cqrs\Fixture\NoOpHandler;
use Dailex\Article\CommandHandler\CreateArticleHandler;
use Dailex\Article\Domain\Article;
use Dailex\Article\Domain\Entity\ArticleEntityType;
use Dailex\Article\Projection\ArticleProjectionType;
use Dailex\MessageBus\CallbackHandler;
use Dailex\MessageBus\LazyHandler;

// /dev/null style persistence implementation for the CommitStream
final class EchoPersistenceAdapter implements PersistenceAdapterInterface
{
    /**
     * @param StreamId $streamId
     * @param Revision|null $revision
     * @return CommitStreamInterface
     */
    public function loadStream(StreamId $streamId, Revision $revision = null): CommitStreamInterface
    {
        echo "<h4>".__METHOD__ . " streamId: $streamId, revision: $revision</h4>";
        return CommitStream::fromStreamId($streamId);
    }

    /**
     * @param CommitStreamInterface $stream
     * @param Revision $storeHead
     * @return bool
     */
    public function storeStream(CommitStreamInterface $stream, Revision $storeHead): bool
    {
        echo "<h4>".__METHOD__ . " streamId: ". $stream->getStreamId().", revision: ".$stream->getStreamRevision()."</h4>";
        return true;
    }
}

// used to pass the message-bus by reference to any command-handler factories
$messageBus = null;
// create unit-of-work for article AR's
$articleUow = new UnitOfWork(Article::class, new EchoPersistenceAdapter);
// create in-process transport used for all subscriptions
$transport = new InProcessTransport("inproc");
$commandHandlerFactory = function () use (&$messageBus, $articleUow) {
    return new CreateArticleHandler(new ArticleEntityType, $articleUow, $messageBus);
};
// setup command channel
$commandHandlers = new MessageHandlerList([ new LazyHandler($commandHandlerFactory) ]);
$commandSub = new Subscription("command-sub", $transport, $commandHandlers);
$commandChannel = new Channel("commands", new SubscriptionMap([ $commandSub ]));
// setup commit channel
$commitHandlers = new MessageHandlerList([ new StandardProjector(new ArticleProjectionType) ]);
$commitSub = new Subscription("commit-sub", $transport, $commitHandlers);
$commitChannel = new Channel("commits", new SubscriptionMap([ $commitSub ]));
// setup event channel
$eventHandlers = new MessageHandlerList([ new CallbackHandler(function (EnvelopeInterface $envelope): bool {
    echo "<h4>Received message '".get_class($envelope->getMessage())."' on (post-commit)event channel, but not doing anything with it atm.</h4>";
    return true;
}) ]);
$eventSub = new Subscription("event-sub", $transport, $eventHandlers);
$eventChannel = new Channel("events", new SubscriptionMap([ $eventSub ]));
// initialize message-bus
$messageBus = new MessageBus(new ChannelMap([ $commandChannel, $commitChannel, $eventChannel ]));
return $messageBus;
