<?php

namespace Testing\Blog\Article\Domain\Command;

use Daikon\Cqrs\Aggregate\AggregateId;
use Daikon\Cqrs\Aggregate\Command;
use Daikon\Entity\ValueObject\Text;
use Daikon\MessageBus\MessageInterface;

final class CreateArticle extends Command
{
    /**
     * @var Text $title
     */
    private $title;

    /**
     * @var Text $content
     */
    private $content;

    /**
     * @param  mixed[] $nativeValues
     * @return MessageInterface
     */
    public static function fromArray(array $nativeValues): MessageInterface
    {
        return new self(
            AggregateId::fromNative($nativeValues['aggregateId']),
            Text::fromNative($nativeValues['title']),
            Text::fromNative($nativeValues['content'])
        );
    }

    /**
     * @return Text
     */
    public function getTitle(): Text
    {
        return $this->title;
    }

    /**
     * @return Text
     */
    public function getContent(): Text
    {
        return $this->content;
    }

    /**
     * @return mixed[]
     */
    public function toArray(): array
    {
        $arr = parent::toArray();
        $arr['title'] = $this->title->toNative();
        $arr['content'] = $this->content->toNative();
        return $arr;
    }

    /**
     * @param Text $title
     * @param Text $content
     */
    protected function __construct(AggregateId $aggregateId, Text $title, Text $content)
    {
        parent::__construct($aggregateId);
        $this->title = $title;
        $this->content = $content;
    }
}
