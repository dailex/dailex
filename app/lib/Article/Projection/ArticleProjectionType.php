<?php

namespace Dailex\Article\Projection;

use Daikon\Cqrs\Aggregate\AggregateId;
use Daikon\Entity\EntityType\Attribute;
use Daikon\Entity\EntityType\EntityType;
use Daikon\Entity\Entity\TypedEntityInterface;
use Daikon\Entity\ValueObject\Text;

final class ArticleProjectionType extends EntityType
{
    public function __construct()
    {
        parent::__construct("ArticleProjection", [
            Attribute::define("id", AggregateId::class, $this),
            Attribute::define("revision", Revision::class, $this),
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
        return ArticleProjection::fromArray($articleState);
    }
}
