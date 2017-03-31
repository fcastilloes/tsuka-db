<?php

namespace Tsuka\DB;

use Tsuka\DB\Binder\BinderInterface;

abstract class Entity
{
    /**
     * Return the entity visible fields.
     *
     * @return array
     */
    abstract public function getFields(): array;

    public static function createFromData(array $data)
    {
        $instance = new static();
        $instance->hydrate($data);

        return $instance;
    }

    /**
     * Return the relations of the entity.
     *
     * The keys are the name of the relations and the values are Binder
     * instances.
     *
     * Overwrite to provide a property list of fields representing simple
     * relations.
     *
     * @return BinderInterface[]
     */
    public function getRelations(): array
    {
        return [];
    }

    /**
     * @param array $data
     * @return $this
     */
    public function hydrate(array $data)
    {
        foreach ($data as $key => $value) {
            if (!property_exists($this, $key)) {
                continue;
            }

            $this->$key = $value;
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getArray()
    {
        return array_intersect_key(
            get_object_vars($this),
            array_flip($this->getFields())
        );
    }
}
