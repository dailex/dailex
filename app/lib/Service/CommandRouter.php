<?php

namespace Dailex\Service;

use Auryn\Injector;
use Daikon\Cqrs\EventStore\UnitOfWorkMap;
use Daikon\MessageBus\Channel\Subscription\MessageHandler\MessageHandlerInterface;
use Daikon\MessageBus\EnvelopeInterface;
use Dailex\Util\StringToolkit;

final class CommandRouter implements MessageHandlerInterface
{
    private $injector;

    private $unitOfWorkMap;

    public function __construct(Injector $injector, UnitOfWorkMap $unitOfWorkMap)
    {
        $this->injector = $injector;
        $this->unitOfWorkMap = $unitOfWorkMap;
    }

    public function handle(EnvelopeInterface $envelope): bool
    {
        $commandClass = get_class($envelope->getMessage());
        $commandHandlerClass = $commandClass.'Handler';
        $typePrefix = StringToolkit::getAggregateRootPrefix($commandClass::getAggregateRootClass());
        $unitOfWork = $this->unitOfWorkMap->get($typePrefix.'.commit_stream.event_source');

        return $this->injector
            ->share($commandHandlerClass)
            ->make($commandHandlerClass, [':unitOfWork' => $unitOfWork])
            ->handle($envelope);
    }
}
