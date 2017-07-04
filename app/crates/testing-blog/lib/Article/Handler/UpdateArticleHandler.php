<?php

namespace Testing\Blog\Article\Handler;

use Daikon\Cqrs\Aggregate\CommandHandler;
use Daikon\MessageBus\Metadata\Metadata;
use Testing\Blog\Article\Domain\Article;
use Testing\Blog\Article\Domain\Command\UpdateArticle;

final class UpdateArticleHandler extends CommandHandler
{
    protected function handleUpdateArticle(UpdateArticle $updateArticle, Metadata $metadata): bool
    {
        $article = $this->checkout($updateArticle->getAggregateId());
        return true;
    }
}
