<?php

namespace Dailex\Article\Domain;

use Daikon\Cqrs\Aggregate\AggregateIdInterface;
use Daikon\Cqrs\Aggregate\AggregateRoot;
use Dailex\Article\Domain\Command\CreateArticle;
use Dailex\Article\Domain\Entity\ArticleEntityType;
use Dailex\Article\Domain\Event\ArticleWasCreated;

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
            ->withId($articleWasCreated->getAggregateId())
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
