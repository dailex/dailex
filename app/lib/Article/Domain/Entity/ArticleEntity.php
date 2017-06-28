<?php

namespace Dailex\Article\Domain\Entity;

use Daikon\Cqrs\Aggregate\AggregateId;
use Daikon\Entity\Entity\Entity;
use Daikon\Entity\ValueObject\Text;
use Daikon\Entity\ValueObject\ValueObjectInterface;

class ArticleEntity extends Entity
{
    /**
     * @return ValueObjectInterface
     */
    public function getIdentity(): ValueObjectInterface
    {
        return $this->getId();
    }

    /**
     * @return AggregateId
     */
    public function getId(): AggregateId
    {
        return $this->get("id");
    }

    /**
     * @param AggregateId $aggregateId
     * @return self
     */
    public function withId(AggregateId $aggregateId): self
    {
        return $this->withValue("id", $aggregateId);
    }

    /**
     * @return Text
     */
    public function getTitle(): Text
    {
        return $this->get("title");
    }

    /**
     * @param Text $text
     * @return self
     */
    public function withTitle(Text $title): self
    {
        return $this->withValue("title", $title);
    }

    /**
     * @return Text
     */
    public function getContent(): Text
    {
        return $this->get("content");
    }

    /**
     * @param Text $content
     * @return self
     */
    public function withContent(Text $content): self
    {
        return $this->withValue("content", $content);
    }
}
