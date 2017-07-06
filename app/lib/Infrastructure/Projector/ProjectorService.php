<?php

namespace Dailex\Infrastructure\Projector;

use Assert\Assertion;
use Daikon\Cqrs\Aggregate\AggregatePrefix;
use Daikon\Cqrs\EventStore\CommitInterface;
use Daikon\MessageBus\EnvelopeInterface;
use Dailex\Exception\RuntimeException;

final class ProjectorService implements ProjectorServiceInterface
{
    private $projectorMap;

    public function __construct(ProjectorMap $projectorMap)
    {
        $this->projectorMap = $projectorMap;
    }

    public function handle(EnvelopeInterface $envelope): bool
    {
        $commit = $envelope->getMessage();
        Assertion::implementsInterface($commit, CommitInterface::class);

        foreach ($commit->getEventLog() as $domainEvent) {
            $aggregatePrefix = AggregatePrefix::fromFqcn($domainEvent->getAggregateRootClass());
            $projectors = $this->projectorMap->filterByAggregatePrefix($aggregatePrefix);
            foreach ($projectors->getIterator() as $projector) {
                if (!$projector->handle($envelope)) {
                    throw new RuntimeException('Projector %s failed to handle message.');
                }
            }
        }

        return true;
    }
}
