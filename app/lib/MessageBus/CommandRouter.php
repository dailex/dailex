<?php

namespace Dailex\MessageBus;

use Auryn\Injector;
use Daikon\MessageBus\Channel\Subscription\MessageHandler\MessageHandlerInterface;
use Daikon\MessageBus\EnvelopeInterface;
use Daikon\MessageBus\MessageBusInterface;
use Dailex\Infrastructure\DataAccess\UnitOfWorkMap;

final class CommandRouter implements MessageHandlerInterface
{
    private $injector;

    private $unitOfWorkMap;

    private $messageBus;

    public function __construct(Injector $injector, UnitOfWorkMap $unitOfWorkMap, MessageBusInterface $messageBus)
    {
        $this->injector = $injector;
        $this->unitOfWorkMap = $unitOfWorkMap;
        $this->messageBus = $messageBus;
    }

    public function handle(EnvelopeInterface $envelope): bool
    {
        $commandHandlerClass = get_class($envelope->getMessage()).'Handler';
        $typePrefix = explode('-', $envelope->getMessage()->getAggregateId(), 2)[0];

        $state = [
            ':articleType' => new \Testing\Blog\Article\Domain\Entity\ArticleEntityType,
            ':unitOfWork' => $this->unitOfWorkMap->get($typePrefix.'::domain_event::event_source::unit_of_work'),
            ':messageBus' => $this->messageBus
        ];

        $commandHandler = $this->injector
            ->define($commandHandlerClass, $state)
            ->make($commandHandlerClass);

        return $commandHandler->handle($envelope);
    }
}
