<?php

namespace Tsuka\DB\Binder;

use Kusanagi\Sdk\Action;
use Tsuka\DB\Entity;

interface BinderInterface
{
    /**
     * Perform the operations to relate an Entity.
     *
     * @param Action $action
     * @param Entity $entity
     * @param string $relation
     */
    public function bind(Action $action, Entity $entity, string $relation);
}
