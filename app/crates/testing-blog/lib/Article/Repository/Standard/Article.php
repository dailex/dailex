<?php

namespace Testing\Blog\Article\Repository\Standard;

use Dailex\Infrastructure\Projection\ProjectionInterface;
use Dailex\Infrastructure\Projection\ProjectionTrait;
use Testing\Blog\Article\Domain\Event\ArticleWasCreated;
use Testing\Blog\Article\Domain\Event\ArticleWasUpdated;

final class Article implements ProjectionInterface
{
    use ProjectionTrait;

    public function getTitle()
    {
        return $this->state['title'];
    }

    public function getContent()
    {
        return $this->state['content'];
    }

    private function whenArticleWasCreated(ArticleWasCreated $articleWasCreated)
    {
        return self::fromArray(
            [
                'aggregateId' => $articleWasCreated->getAggregateId()->toNative(),
                'aggregateRevision' => $articleWasCreated->getAggregateRevision()->toNative(),
                'title' => $articleWasCreated->getTitle()->toNative(),
                'content' => $articleWasCreated->getContent()->toNative()
            ]
        );
    }

    private function whenArticleWasUpdated(ArticleWasUpdated $articleWasUpdated)
    {
        return self::fromArray(array_merge(
            $this->state,
            [
                'aggregateRevision' => $articleWasUpdated->getAggregateRevision()->toNative(),
                'title' => $articleWasUpdated->getTitle()->toNative(),
                'content' => $articleWasUpdated->getContent()->toNative()
            ]
        ));
    }
}