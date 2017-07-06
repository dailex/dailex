<?php

namespace Testing\Blog\Article\Domain\Event;

use Daikon\Cqrs\Aggregate\AggregateId;
use Daikon\Cqrs\Aggregate\AggregateRevision;
use Daikon\Cqrs\Aggregate\DomainEvent;
use Daikon\Entity\ValueObject\Text;
use Daikon\MessageBus\MessageInterface;
use Testing\Blog\Article\Domain\Article;
use Testing\Blog\Article\Domain\Command\UpdateArticle;

final class ArticleWasUpdated extends DomainEvent
{
    private $title;

    private $content;

    public static function getAggregateRootClass(): string
    {
        return Article::class;
    }

    public static function fromArray(array $nativeValues): MessageInterface
    {
        return new self(
            AggregateId::fromNative($nativeValues['aggregateId']),
            Text::fromNative($nativeValues['title']),
            Text::fromNative($nativeValues['content']),
            AggregateRevision::fromNative($nativeValues['aggregateRevision'])
        );
    }

    public static function viaCommand(UpdateArticle $updateArticle): self
    {
        return new self(
            $updateArticle->getAggregateId(),
            $updateArticle->getTitle(),
            $updateArticle->getContent()
        );
    }

    public function getTitle(): Text
    {
        return $this->title;
    }

    public function getContent(): Text
    {
        return $this->content;
    }

    public function toArray(): array
    {
        $arr['aggregateId'] = $this->getAggregateId()->toNative();
        $arr['aggregateRevision'] = $this->getAggregateRevision()->toNative();
        $arr['title'] = $this->title->toNative();
        $arr['content'] = $this->content->toNative();
        return $arr;
    }

    public static function getAggregateRootClass(): string
    {
        return Article::class;
    }

    protected function __construct(
        AggregateId $aggregateId,
        Text $title,
        Text $content,
        AggregateRevision $revision = null
    ) {
        parent::__construct($aggregateId, $revision);
        $this->title = $title;
        $this->content = $content;
    }
}
