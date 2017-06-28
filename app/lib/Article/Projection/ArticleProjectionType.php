<?php

namespace Dailex\Article\Projection;

use Daikon\Entity\Entity\TypedEntityInterface;
use Dailex\Article\Domain\Entity\ArticleEntityType;

final class ArticleProjectionType extends ArticleEntityType
{
    public function __construct()
    {
        parent::__construct("ArticleProjection");
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
