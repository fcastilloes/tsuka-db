<?php

namespace Tsuka\DB\Binder;

use Kusanagi\Sdk\Action;
use Tsuka\DB\Entity;

/**
 * Requests a multiple relation of an Entity for an id.
 *
 * The params for the request are fetched from a callable.
 *
 * There is a default callable to set an "id" param for the request.
 *
 * @package Tsuka\DB\Binder
 */
class ReverseListCollectionBinder implements CollectionBinderInterface
{
    /**
     * @var string
     */
    private $version = '';

    /**
     * @var string
     */
    private $action = '';

    /**
     * @var callback
     */
    private $paramResolver;

    /**
     * @param string $version
     * @param string $action
     * @param callable $paramResolver
     */
    public function __construct(
        string $version,
        string $action,
        callable $paramResolver = null
    ) {
        $this->version = $version;
        $this->action = $action;
        $this->paramResolver = $paramResolver ?? function (Action $action, array $collection, string $relation) {
            $ids = array_map(function (Entity $entity) {
                return $entity->getIdentifier();
            }, $collection);
            return [
                $action->newParam('ids', $ids),
            ];
        };
    }

    /**
     * @param Action $action
     * @param Entity[] $collection
     * @param string $relation
     */
    public function bind(Action $action, array $collection, string $relation)
    {
        $action->deferCall(
            $relation,
            $this->version,
            $this->action,
            ($this->paramResolver)($action, $collection, $relation)
        );
    }
}
