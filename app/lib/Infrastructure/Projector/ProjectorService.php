<?php

namespace Dailex\Infrastructure\Projector;

use Assert\Assertion;
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
        Assertion::isInstanceOf($commit, CommitInterface::class);

        $projectors = $this->projectorMap->filterByStreamId($commit->getStreamId());
        foreach ($projectors->getIterator() as $projector) {
            if (!$projector->handle($envelope)) {
                throw new RuntimeException('Projector %s failed to handle message.');
            }
        }

        return true;
    }
}
