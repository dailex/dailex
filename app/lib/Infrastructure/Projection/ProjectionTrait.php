<?php

namespace Dailex\Infrastructure\Projection;

use Daikon\Cqrs\Aggregate\DomainEventInterface;

trait ProjectionTrait
{
    private $state;

    public static function fromArray(array $state = [])
    {
        return new self($state);
    }

    public function getAggregateId(): string
    {
        return $this->state['aggregateId'];
    }

    public function getAggregateRevision(): int
    {
        return $this->state['aggregateRevision'];
    }

    public function toArray(): array
    {
        return $this->state;
    }

    public function applyEvent(DomainEventInterface $domainEvent): ProjectionInterface
    {
        return $this->invokeEventHandler($domainEvent);
    }

    private function invokeEventHandler(DomainEventInterface $event): ProjectionInterface
    {
        $handlerName = preg_replace('/Event$/', '', (new \ReflectionClass($event))->getShortName());
        $handlerMethod = 'when'.ucfirst($handlerName);
        $handler = [$this, $handlerMethod];
        if (!is_callable($handler)) {
            throw new \Exception("Handler '$handlerMethod' is not callable on ".self::class);
        }
        return call_user_func($handler, $event);
    }

    private function __construct(array $state = [])
    {
        $this->state = $state;
    }
}
