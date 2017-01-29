<?php

namespace Tsuka\DB;

use PDO;

class DBBuilder
{
    /**
     * @var PDO
     */
    private $pdo;

    /**
     * @var string
     */
    private $table = '';

    /**
     * @var array
     */
    private $simpleRelations = [];

    /**
     * @var array
     */
    private $multipleRelations = [];

    /**
     * @var array
     */
    private $entity = [];

    /**
     * @param PDO $pdo
     */
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * @param string $table
     * @return $this
     */
    public function prepareTable($table)
    {
        $this->table = $table;

        return $this;
    }

    /**
     * @param string $relation
     * @return $this
     */
    public function relateOne($relation)
    {
        $this->simpleRelations[] = $relation;

        return $this;
    }

    /**
     * @param string $relation
     * @return $this
     */
    public function relateMany($relation)
    {
        $this->multipleRelations[] = $relation;

        return $this;
    }

    /**
     * @param array $entity
     * @return $this
     */
    public function setEntity(array $entity)
    {
        $this->entity = $entity;

        return $this;
    }

    /**
     * @return DBService
     */
    public function build()
    {
        $service = new DBService(
            $this->pdo,
            $this->table,
            $this->entity,
            $this->simpleRelations,
            $this->multipleRelations
        );
        $this->table = '';
        $this->simpleRelations = [];
        $this->multipleRelations = [];
        $this->entity = [];

        return $service;
    }
}
