<?php

namespace Dailex\Article\Projection;

use Daikon\Cqrs\Projection\ProjectionInterface;
use Daikon\Cqrs\Projection\ProjectionTrait;
use Dailex\Article\Domain\Entity\ArticleEntity;
use Dailex\Article\Domain\Event\ArticleWasCreated;

final class ArticleProjection extends ArticleEntity implements ProjectionInterface
{
    use ProjectionTrait;

    /**
     * @param ArticleWasCreated $articleWasCreated
     * @return self
     */
    protected function whenArticleWasCreated(ArticleWasCreated $articleWasCreated): self
    {
        return $this
            ->withId($articleWasCreated->getAggregateId())
            ->withTitle($articleWasCreated->getTitle())
            ->withContent($articleWasCreated->getContent());
    }
}
