<?php

namespace Testing\Blog\Article\Handler;

use Daikon\EventSourcing\Aggregate\CommandHandler;
use Daikon\MessageBus\Metadata\Metadata;
use Testing\Blog\Article\Domain\Command\UpdateArticle;

final class UpdateArticleHandler extends CommandHandler
{
    protected function handleUpdateArticle(UpdateArticle $updateArticle, Metadata $metadata): array
    {
        $article = $this->checkout($updateArticle->getAggregateId(), $updateArticle->getKnownAggregateRevision());
        return [$article->update($updateArticle), $metadata];
    }
}
