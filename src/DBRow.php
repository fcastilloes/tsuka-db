<?php

namespace Tsuka\DB;

use Kusanagi\Sdk\Action;

class DBRow
{
    /**
     * @var Action
     */
    private $action;

    /**
     * @var array
     */
    private $data = [];

    /**
     * @var array
     */
    private $simpleRelations = [];

    /**
     * @var array
     */
    private $multipleRelations = [];

    /**
     * @param Action $action
     * @param array $data
     */
    public function __construct(Action $action, array $data)
    {
        $this->action = $action;
        $this->data = $data;
    }

    /**
     * @param string $field
     */
    public function relateOne($field)
    {
        $this->simpleRelations[] = $field;
    }

    /**
     * @param string $field
     */
    public function relateMany($field)
    {
        $this->multipleRelations[] = $field;
    }

    /**
     *
     */
    public function resolveRelations()
    {
        foreach ($this->simpleRelations as $relation) {
            if (!$this->data[$relation]) {
                continue;
            }

            $this->action->relateOne(
                $this->data['id'],
                $relation,
                $this->data[$relation]
            );

            $this->action->deferCall(
                $relation,
                $this->action->getVersion(),
                'read',
                [
                    $this->action->newParam('id', $this->data[$relation]),
                ]
            );
        }

        foreach ($this->multipleRelations as $relation) {
            if (!$this->data[$relation]) {
                continue;
            }

            $this->action->relateMany(
                $this->data['id'],
                $relation,
                explode(',', $this->data[$relation])
            );

            $this->action->deferCall(
                $relation,
                $this->action->getVersion(),
                'list',
                [
                    $this->action->newParam('ids', $this->data[$relation]),
                ]
            );
        }
    }

    /**
     * @param array $fields
     * @return array
     */
    public function getEntity($fields)
    {
        return array_intersect_key($this->data, array_flip($fields));
    }
}
