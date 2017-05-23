<?php

namespace Tsuka\DB\Binder;

use Katana\Sdk\Action;
use Tsuka\DB\Entity;

interface CollectionBinderInterface
{
    /**
     * Perform the operations to relate a collection.
     *
     * @param Action $action
     * @param Entity[] $entity
     * @param string $relation
     */
    public function bind(Action $action, array $entity, string $relation);
}
