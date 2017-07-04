<?php

namespace Testing\Blog\Article\Domain\Event;

use Daikon\Cqrs\Aggregate\AggregateId;
use Daikon\Cqrs\Aggregate\DomainEvent;
use Daikon\Entity\ValueObject\Text;
use Daikon\MessageBus\MessageInterface;
use Testing\Blog\Article\Domain\Command\CreateArticle;

final class ArticleWasCreated extends DomainEvent
{
    private $title;

    private $content;

    public static function fromArray(array $nativeValues): MessageInterface
    {
        return new self(
            AggregateId::fromNative($nativeValues['aggregateId']),
            Text::fromNative($nativeValues['title']),
            Text::fromNative($nativeValues['content'])
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
        $arr['aggregateId'] = $this->getAggregateId()->toNative();
        $arr['aggregateRevison'] = $this->getAggregateRevision()->toNative();
        $arr['title'] = $this->title->toNative();
        $arr['content'] = $this->content->toNative();
        return $arr;
    }

    protected function __construct(AggregateId $aggregateId, Text $title, Text $content)
    {
        parent::__construct($aggregateId);
        $this->title = $title;
        $this->content = $content;
    }
}
