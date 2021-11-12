<?php

namespace Tsuka\DB\Binder;

use Kusanagi\Sdk\Action;
use Tsuka\DB\Entity;
use Tsuka\DB\Exception\MalformedBinderException;

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

    /**
     * @var string
     */
    public $actionName = '';

    /**
     * @var string
     */
    public $paramName = '';

    /**
     * @var string
     */
    public $version = '';

    /**
     * @var bool
     */
    public $multiple = false;


    /**
     * @param   string  $actionName     The service action's name to be called
     * @param   string  $paramName      The param name to be passed to the service action
     * @param   string  $version        The version to execute the bind against
     * @param   bool    $multiple       Whether or not to use `relateMany`
     *
     * @throws MalformedBinderException
     */
    public function __construct(string $actionName, string $paramName, string $version, bool $multiple = false)
    {
        if (!$actionName || !$paramName) {
            throw new MalformedBinderException('actionName and paramName MUST NOT be empty');
        }
        $this->actionName = $actionName;
        $this->paramName = $paramName;
        $this->version = $version;
        $this->multiple = $multiple;
    }

    /**
     * @param Action $action
     * @param Entity $entity
     * @param string $relation
     */
    public function bind(Action $action, Entity $entity, string $relation)
    {
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
                $entity->id
            );
        }

        $action->deferCall(
            $relation,
            $this->version,
            $this->actionName,
            [
                $action->newParam($this->paramName, $entity->id),
            ]
        );
    }
}
