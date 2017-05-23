<?php

namespace Tsuka\DB\Operation;

use Katana\Sdk\Action;
use Tsuka\DB\Binder\BinderInterface;
use Tsuka\DB\Binder\CollectionBinderInterface;
use Tsuka\DB\Entity;

class CallOperation
{
    /**
     * @var Action
     */
    private $action;

    /**
     * @var array
     */
    private $relations = [];

    /**
     * @param Action $action
     * @param array $relations
     */
    public function __construct(Action $action, array $relations = [])
    {
        $this->action = $action;
        $this->relations = $relations;
    }

    public function operate(Entity $entity)
    {
        $relations = array_intersect_key(
            $entity->getRelations(),
            array_flip($this->relations)
        );

        foreach ($relations as $relation => $binder) {
            if ($binder instanceof BinderInterface) {
                $binder->bind($this->action, $entity, $relation);
            }
        }
    }

    public function operateCollection(array $collection)
    {
        if (!$collection) {
            return;
        }

        $relations = array_intersect_key(
            $collection[0]->getRelations(),
            array_flip($this->relations)
        );

        foreach ($relations as $relation => $binder) {
            if ($binder instanceof CollectionBinderInterface) {
                $binder->bind($this->action, $collection, $relation);
            }
        }
    }
}
