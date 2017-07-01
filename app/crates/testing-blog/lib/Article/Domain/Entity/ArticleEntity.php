<?php

namespace Testing\Blog\Article\Domain\Entity;

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
        return $this->get("identity");
    }

    /**
     * @param AggregateId $aggregateId
     * @return self
     */
    public function withIdentity(AggregateId $aggregateId): self
    {
        return $this->withValue("identity", $aggregateId);
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
