<?php

namespace Dailex\Service;

use Auryn\Injector;
use Daikon\Cqrs\Aggregate\CommandInterface;
use Daikon\Cqrs\EventStore\UnitOfWorkMap;
use Daikon\MessageBus\Channel\Subscription\MessageHandler\MessageHandlerInterface;
use Daikon\MessageBus\EnvelopeInterface;
use Dailex\Exception\RuntimeException;

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
        $command = $envelope->getMessage();
        if (!$command instanceof CommandInterface) {
            throw new RuntimeException(sprintf(
                'Message of type %s must implement %s ',
                get_class($command),
                CommandInterface::class
            ));
        }

        $commandHandlerClass = str_replace('\\Domain\\Command', '\\Handler', get_class($command)).'Handler';
        $unitOfWork = $this->unitOfWorkMap->getByAggregateId($command->getAggregateId());

        return $this->injector
            ->share($commandHandlerClass)
            ->make($commandHandlerClass, [':unitOfWork' => $unitOfWork])
            ->handle($envelope);
    }
}
