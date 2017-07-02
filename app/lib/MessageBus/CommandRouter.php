<?php

namespace Dailex\MessageBus;

use Auryn\Injector;
use Daikon\Cqrs\EventStore\UnitOfWorkMap;
use Daikon\MessageBus\Channel\Subscription\MessageHandler\MessageHandlerInterface;
use Daikon\MessageBus\EnvelopeInterface;
use Daikon\MessageBus\MessageBusInterface;
use Dailex\Util\StringToolkit;

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
        $commandClass = get_class($envelope->getMessage());
        $commandHandlerClass = $commandClass.'Handler';
        $typePrefix = StringToolkit::getAggregateRootPrefix($commandClass::getAggregateRootClass());

        $state = [
            ':unitOfWork' => $this->unitOfWorkMap->get($typePrefix.'::domain_event::event_source::unit_of_work'),
            ':messageBus' => $this->messageBus
        ];

        return $this->injector
            ->define($commandHandlerClass, $state)
            ->make($commandHandlerClass)
            ->handle($envelope);
    }
}
