<?php

namespace Testing\Blog\Article\Domain;

use Daikon\Cqrs\Aggregate\AggregateIdInterface;
use Daikon\Cqrs\Aggregate\AggregateRoot;
use Testing\Blog\Article\Domain\Command\CreateArticle;
use Testing\Blog\Article\Domain\Command\UpdateArticle;
use Testing\Blog\Article\Domain\Entity\ArticleEntityType;
use Testing\Blog\Article\Domain\Event\ArticleWasCreated;
use Testing\Blog\Article\Domain\Event\ArticleWasUpdated;

final class Article extends AggregateRoot
{
    private $articleState;

    public function getIdentifier(): AggregateIdInterface
    {
        return $this->articleState->getIdentity();
    }

    public static function create(CreateArticle $createArticle): self
    {
        return (new self($createArticle->getAggregateId()))
            ->reflectThat(ArticleWasCreated::viaCommand($createArticle));
    }

    public static function update(UpdateArticle $updateArticle): self
    {
        return (new self($updateArticle->getAggregateId()))
            ->reflectThat(ArticleWasUpdated::viaCommand($updateArticle));
    }

    protected function whenArticleWasCreated(ArticleWasCreated $articleWasCreated)
    {
        $this->articleState = $this->articleState
            ->withIdentity($articleWasCreated->getAggregateId())
            ->withTitle($articleWasCreated->getTitle())
            ->withContent($articleWasCreated->getContent());
    }

    protected function whenArticleWasUpdated(ArticleWasUpdated $articleWasUpdated)
    {
        $this->articleState = $this->articleState
            ->withTitle($articleWasUpdated->getTitle())
            ->withContent($articleWasUpdated->getContent());
    }

    protected function __construct(AggregateIdInterface $aggregateId)
    {
        parent::__construct($aggregateId);
        $this->articleState = (new ArticleEntityType)->makeEntity(["identity" => $aggregateId]);
    }
}
