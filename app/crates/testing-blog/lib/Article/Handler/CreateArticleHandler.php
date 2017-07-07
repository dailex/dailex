<?php

namespace Testing\Blog\Article\Handler;

use Daikon\EventSourcing\Aggregate\CommandHandler;
use Daikon\MessageBus\Metadata\Metadata;
use Testing\Blog\Article\Domain\Article;
use Testing\Blog\Article\Domain\Command\CreateArticle;

final class CreateArticleHandler extends CommandHandler
{
    protected function handleCreateArticle(CreateArticle $createArticle, Metadata $metadata): bool
    {
        return $this->commit(
            Article::create($createArticle),
            $metadata
        );
    }
}
