<?php

namespace Testing\Blog\Article\Domain\Event;

use Daikon\EventSourcing\Aggregate\AggregateId;
use Daikon\EventSourcing\Aggregate\AggregateRevision;
use Daikon\EventSourcing\Aggregate\DomainEvent;
use Daikon\Entity\ValueObject\Text;
use Daikon\MessageBus\MessageInterface;
use Testing\Blog\Article\Domain\Article;
use Testing\Blog\Article\Domain\Command\CreateArticle;

final class ArticleWasCreated extends DomainEvent
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

    public static function viaCommand(CreateArticle $createArticle): self
    {
        return new self(
            $createArticle->getAggregateId(),
            $createArticle->getTitle(),
            $createArticle->getContent()
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
        return array_merge([
            'title' => $this->title->toNative(),
            'content' => $this->content->toNative()
        ], parent::toArray());
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
