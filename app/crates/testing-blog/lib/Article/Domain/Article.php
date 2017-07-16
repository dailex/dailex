<?php

namespace Testing\Blog\Article\Domain;

use Daikon\EventSourcing\Aggregate\AggregateAlias;
use Daikon\EventSourcing\Aggregate\AggregateRootInterface;
use Daikon\EventSourcing\Aggregate\AggregateRootTrait;
use Testing\Blog\Article\Domain\Command\CreateArticle;
use Testing\Blog\Article\Domain\Command\UpdateArticle;
use Testing\Blog\Article\Domain\Entity\ArticleEntityType;
use Testing\Blog\Article\Domain\Event\ArticleWasCreated;
use Testing\Blog\Article\Domain\Event\ArticleWasUpdated;

final class Article implements AggregateRootInterface
{
    use AggregateRootTrait;

    private $articleState;

    public static function getAlias(): AggregateAlias
    {
        return AggregateAlias::fromNative('testing.blog.article');
    }

    public static function create(CreateArticle $createArticle): self
    {
        return (new self($createArticle->getAggregateId()))
            ->reflectThat(ArticleWasCreated::viaCommand($createArticle));
    }

    public function update(UpdateArticle $updateArticle): self
    {
        return $this->reflectThat(ArticleWasUpdated::viaCommand($updateArticle));
    }

    protected function whenArticleWasCreated(ArticleWasCreated $articleWasCreated)
    {
        $this->articleState = (new ArticleEntityType)->makeEntity([
            'identity' => $articleWasCreated->getAggregateId(),
            'title' => $articleWasCreated->getTitle(),
            'content' => $articleWasCreated->getContent()
        ]);
    }

    protected function whenArticleWasUpdated(ArticleWasUpdated $articleWasUpdated)
    {
        $this->articleState = $this->articleState
            ->withTitle($articleWasUpdated->getTitle())
            ->withContent($articleWasUpdated->getContent());
    }
}
