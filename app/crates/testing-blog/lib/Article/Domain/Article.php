<?php

namespace Testing\Blog\Article\Domain;

use Daikon\Cqrs\Aggregate\AggregateIdInterface;
use Daikon\Cqrs\Aggregate\AggregateRoot;
use Testing\Blog\Article\Domain\Command\CreateArticle;
use Testing\Blog\Article\Domain\Entity\ArticleEntityType;
use Testing\Blog\Article\Domain\Event\ArticleWasCreated;

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

    protected function whenArticleWasCreated(ArticleWasCreated $articleWasCreated)
    {
        $this->articleState = $this->articleState
            ->withIdentity($articleWasCreated->getAggregateId())
            ->withTitle($articleWasCreated->getTitle())
            ->withContent($articleWasCreated->getContent());
    }

    protected function __construct(AggregateIdInterface $aggregateId)
    {
        parent::__construct($aggregateId);
        $this->articleState = (new ArticleEntityType)->makeEntity(["identity" => $aggregateId]);
    }
}
