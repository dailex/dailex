<?php

namespace Dailex\MessageBus;

use Auryn\Injector;
use Daikon\MessageBus\Channel\Subscription\MessageHandler\MessageHandlerInterface;
use Daikon\MessageBus\EnvelopeInterface;

final class CommandRouter implements MessageHandlerInterface
{
    private $injector;

    public function __construct(Injector $injector)
    {
        $this->injector = $injector;
    }

    public function handle(EnvelopeInterface $envelope): bool
    {
        $commandHandlerClass = get_class($envelope->getMessage()).'Handler';
//         $commandHandler = $this->injector->make($commandHandlerClass);

        $commandHandler = new $commandHandlerClass(
            new \Dailex\Article\Domain\Entity\ArticleEntityType,
            new \Daikon\Cqrs\EventStore\UnitOfWork(
                \Dailex\Article\Domain\ArticleEntityType::class,
                new \Dailex\Article\EchoPersistenceAdapter,
                new \Daikon\Cqrs\EventStore\NoopStreamProcessor
            ),
            $this->injector->make(\Daikon\MessageBus\MessageBus::class)
        );

        return $commandHandler->handle($envelope);
    }
}
