<?php

namespace Tsuka\DB;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;

class DBBuilder
{
    /**
     * @var Connection
     */
    private $connection;

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
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->connection = DriverManager::getConnection($config);
    }

    /**
     * @return Connection
     */
    public function getConnection()
    {
        return $this->connection;
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
            $this->connection,
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
