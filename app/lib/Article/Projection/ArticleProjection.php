<?php

namespace Dailex\Article\Projection;

use Daikon\Cqrs\Projection\ProjectionInterface;
use Daikon\Cqrs\Projection\ProjectionTrait;
use Daikon\Entity\Entity\Entity;
use Dailex\Article\Domain\Event\ArticleWasCreated;

final class ArticleProjection extends Entity implements ProjectionInterface
{
    use ProjectionTrait;

    /**
     * @param ArticleWasCreated $articleWasCreated
     * @return self
     */
    protected function whenArticleWasCreated(ArticleWasCreated $articleWasCreated): self
    {
        return $this
            ->withValue("id", $articleWasCreated->getAggregateId())
            ->withValue("title", $articleWasCreated->getTitle())
            ->with("content", $articleWasCreated->getContent());
    }
}
