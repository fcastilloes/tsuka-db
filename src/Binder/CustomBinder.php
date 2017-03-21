<?php

namespace Tsuka\DB\Binder;

use Katana\Sdk\Action;
use Tsuka\DB\Entity;

/**
 * Sets either a multiple or a single relation of an Entity with a custom id field.
 *
 * The Entity must have an id property to set the relationship.
 *
 * The custom id or ids are looked for in an Entity field with a custom relation name.
 *
 * This Binder relies on a service named as the relation with a custom action
 * that accepts such custom parameter with a comma separated list.
 *
 * @package Tsuka\DB\Binder
 */
class CustomBinder implements BinderInterface
{

    public $actionName = '';
    public $paramName = '';
    public $multiple = false;


    /**
     * @param string $actionName
     * @param string $paramName
     * @param bool $multiple
     */
    public function __construct(string $actionName, string $paramName, bool $multiple)
    {
        $this->actionName = $actionName;
        $this->paramName = $paramName;
        $this->multiple = $multiple;
    }

    /**
     * @param Action $action
     * @param Entity $entity
     * @param string $relation
     */
    public function bind(Action $action, Entity $entity, string $relation)
    {
        if (!$this->actionName || !$this->paramName) {
            return;
        }
        if (!$entity->$relation) {
            return;
        }

        if ($this->multiple) {
            $action->relateMany(
                $entity->id,
                $relation,
                explode(',', $entity->$relation)
            );
        } else {
            $action->relateOne(
                $entity->id,
                $relation,
                explode(',', $entity->$relation)
            );
        }

        $action->deferCall(
            $relation,
            $action->getVersion(),
            $this->actionName,
            [
                $action->newParam($this->paramName, $entity->$relation),
            ]
        );
    }
}
