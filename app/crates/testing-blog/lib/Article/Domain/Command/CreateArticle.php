<?php

namespace Testing\Blog\Article\Domain\Command;

use Daikon\Entity\ValueObject\Text;
use Daikon\EventSourcing\Aggregate\AggregateId;
use Daikon\EventSourcing\Aggregate\AggregateIdInterface;
use Daikon\EventSourcing\Aggregate\Command;
use Daikon\MessageBus\MessageInterface;
use Testing\Blog\Article\Domain\Article;

final class CreateArticle extends Command
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
        return array_merge([
            'title' => $this->title->toNative(),
            'content' => $this->content->toNative()
        ], parent::toArray());
    }

    public static function getAggregateRootClass(): string
    {
        return Article::class;
    }

    protected function __construct(AggregateIdInterface $aggregateId, Text $title, Text $content)
    {
        parent::__construct($aggregateId);
        $this->title = $title;
        $this->content = $content;
    }
}
