<?php

namespace Testing\Blog\Article\Domain\Command;

use Daikon\Cqrs\Aggregate\AggregateId;
use Daikon\Cqrs\Aggregate\Command;
use Daikon\Entity\ValueObject\Text;
use Daikon\MessageBus\MessageInterface;
use Testing\Blog\Article\Domain\Article;

final class UpdateArticle extends Command
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
        $arr['title'] = $this->title->toNative();
        $arr['content'] = $this->content->toNative();
        return $arr;
    }

    public static function getAggregateRootClass(): string
    {
        return Article::class;
    }

    protected function __construct(AggregateId $aggregateId, Text $title, Text $content)
    {
        parent::__construct($aggregateId);
        $this->title = $title;
        $this->content = $content;
    }
}
