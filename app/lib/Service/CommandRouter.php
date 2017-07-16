<?php

namespace Dailex\Service;

use Assert\Assertion;
use Auryn\Injector;
use Daikon\EventSourcing\Aggregate\AggregateAlias;
use Daikon\EventSourcing\Aggregate\CommandInterface;
use Daikon\EventSourcing\EventStore\UnitOfWorkMap;
use Daikon\MessageBus\Channel\Subscription\MessageHandler\MessageHandlerInterface;
use Daikon\MessageBus\EnvelopeInterface;

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
        Assertion::implementsInterface($command, CommandInterface::class);

        $commandHandlerClass = str_replace('\\Domain\\Command', '\\Handler', get_class($command)).'Handler';
        $fqcn = $command->getAggregateRootClass();
        $aggregateAlias = $fqcn::getAlias();
        $unitOfWork = $this->unitOfWorkMap->getByAggregateAlias($aggregateAlias);

        return $this->injector
            ->share($commandHandlerClass)
            ->make($commandHandlerClass, [':unitOfWork' => $unitOfWork])
            ->handle($envelope);
    }
}
