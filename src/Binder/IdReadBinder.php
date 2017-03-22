<?php

namespace Tsuka\DB\Binder;

use Katana\Sdk\Action;
use Tsuka\DB\Entity;

/**
 * Set a single relation of an Entity with an id.
 *
 * The Entity must have an id property to set the relationship.
 *
 * The id is looked for in an Entity field with the name of the
 * relation.
 *
 * This Binder relies on a service named as the relation with a "read" action
 * that accepts an "id" parameter with a comma separated list.
 *
 * @package Tsuka\DB\Binder
 */
class IdReadBinder implements BinderInterface
{
    /**
     * @param Action $action
     * @param Entity $entity
     * @param string $relation
     */
    public function bind(Action $action, Entity $entity, string $relation)
    {
        if (!$entity->$relation) {
            return;
        }

        $action->relateOne(
            $entity->id,
            $relation,
            $entity->$relation
        );

        $action->deferCall(
            $relation,
            $action->getVersion(),
            'read',
            [
                $action->newParam('id', $entity->$relation),
            ]
        );
    }
}
