<?php

namespace Tsuka\DB\Operation;

use Katana\Sdk\Action;
use Tsuka\DB\Binder\IdListBinder;
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

        /** @var IdListBinder $binder */
        foreach ($relations as $relation => $binder) {
            $binder->bind($this->action, $entity, $relation);
        }
    }
}
