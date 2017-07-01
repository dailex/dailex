<?php

namespace Testing\Blog\Article\Domain\Entity;

use Daikon\Cqrs\Aggregate\AggregateId;
use Daikon\Cqrs\Aggregate\AggregateRevision;
use Daikon\Entity\EntityType\Attribute;
use Daikon\Entity\EntityType\EntityType;
use Daikon\Entity\Entity\TypedEntityInterface;
use Daikon\Entity\ValueObject\Text;

class ArticleEntityType extends EntityType
{
    public function __construct()
    {
        parent::__construct("Article", [
            Attribute::define("identity", AggregateId::class, $this),
            Attribute::define("revision", AggregateRevision::class, $this),
            Attribute::define("title", Text::class, $this),
            Attribute::define("content", Text::class, $this),
        ]);
    }

    /**
     * @param mixed[] $articleState
     * @param TypedEntityInterface|null $parent
     * @return TypedEntityInterface
     */
    public function makeEntity(array $articleState = [], TypedEntityInterface $parent = null): TypedEntityInterface
    {
        $articleState["@type"] = $this;
        $articleState["@parent"] = $parent;
        return ArticleEntity::fromArray($articleState);
    }
}
