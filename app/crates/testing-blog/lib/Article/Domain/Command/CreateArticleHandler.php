<?php

namespace Testing\Blog\Article\Domain\Command;

use Daikon\Cqrs\Aggregate\CommandHandler;
use Daikon\Cqrs\EventStore\UnitOfWorkInterface;
use Daikon\MessageBus\MessageBusInterface;
use Daikon\MessageBus\Metadata\Metadata;
use Testing\Blog\Article\Domain\Article;
use Testing\Blog\Article\Domain\Command\CreateArticle;
use Testing\Blog\Article\Domain\Entity\ArticleEntityType;

final class CreateArticleHandler extends CommandHandler
{
    /**
     * @var ArticleEntityType
     */
    private $articleType;

    /**
     * @param ArticleEntityType $articleType
     * @param UnitOfWorkInterface $unitOfWork
     * @param MessageBusInterface $messageBus
     */
    public function __construct(
        ArticleEntityType $articleType,
        UnitOfWorkInterface $unitOfWork,
        MessageBusInterface $messageBus
    ) {
        parent::__construct($unitOfWork, $messageBus);
        $this->articleType = $articleType;
    }

    /**
     * @param CreateArticle $createArticle
     * @param Metadata $metadata
     * @return bool
     */
    protected function handleCreateArticle(CreateArticle $createArticle, Metadata $metadata): bool
    {
        return $this->commit(
            Article::create($createArticle, $this->articleType),
            $metadata
        );
    }
}
