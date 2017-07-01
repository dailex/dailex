<?php

namespace Testing\Blog\Article\Domain;

use Daikon\Cqrs\Aggregate\AggregateIdInterface;
use Daikon\Cqrs\Aggregate\AggregateRoot;
use Testing\Blog\Article\Domain\Command\CreateArticle;
use Testing\Blog\Article\Domain\Entity\ArticleEntityType;
use Testing\Blog\Article\Domain\Event\ArticleWasCreated;

final class Article extends AggregateRoot
{
    /**
     * @var \Dailex\Article\Domain\Entity\ArticleEntity
     */
    private $articleState;

    /**
     * @return AggregateIdInterface
     */
    public function getIdentifier(): AggregateIdInterface
    {
        return $this->articleState->getIdentity();
    }

    /**
     * @param CreateArticle $createArticle
     * @param ArticleEntityType $articleType
     * @return self
     */
    public static function create(CreateArticle $createArticle, ArticleEntityType $articleType): self
    {
        return (new self($createArticle->getAggregateId(), $articleType))
            ->reflectThat(ArticleWasCreated::viaCommand($createArticle));
    }

    /**
     * @param  ArticleWasCreated $articleWasCreated
     * @return self
     */
    protected function whenArticleWasCreated(ArticleWasCreated $articleWasCreated)
    {
        $this->articleState = $this->articleState
            ->withIdentity($articleWasCreated->getAggregateId())
            ->withTitle($articleWasCreated->getTitle())
            ->withContent($articleWasCreated->getContent());
    }

    /**
     * @param ArticleEntityType $articleType
     */
    protected function __construct(AggregateIdInterface $aggregateId, ArticleEntityType $articleType)
    {
        parent::__construct($aggregateId);
        $this->articleState = $articleType->makeEntity([ "identity" => $aggregateId ]);
    }
}
