<?php

namespace Tsuka\DB\Binder;

use Katana\Sdk\Action;
use Tsuka\DB\Entity;

/**
 * Set a multiple relation of an Entity with a list of ids.
 *
 * The Entity must have an id property to set the relationship.
 *
 * The list of ids is looked for in an Entity field with the name of the
 * relation as a comma separated list.
 *
 * This Binder relies on a service named as the relation with a "list" action
 * that accepts an "ids" parameter with a comma separated list.
 *
 * @package Tsuka\DB\Binder
 */
class IdListBinder implements BinderInterface
{
     /**
     * @var string
     */
    public $version = '';


    /**
     * @param   string  $version        The version to execute the bind against
     */
    public function __construct(string $version)
    {
        $this->version = $version;
    }

    /**
     * @param Action $action
     * @param Entity $entity
     * @param string $relation
     */
    public function bind(Action $action, Entity $entity, string $relation, string $version)
    {
        if (!$entity->$relation) {
            return;
        }

        $action->relateMany(
            $entity->id,
            $relation,
            explode(',', $entity->$relation)
        );

        $action->deferCall(
            $relation,
            $this->version,
            'list',
            [
                $action->newParam('ids', $entity->$relation),
            ]
        );
    }
}
