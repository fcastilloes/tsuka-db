<?php

namespace Tsuka\DB;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ConnectionException;
use Doctrine\DBAL\DriverManager;

class DBBuilder
{
    const CONNECTION_OK = 'ok';
    const CONNECTION_KO = 'ko';
    const CONNECTION_GONE = 'gone';

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

    public function testConnection()
    {
        try {
            if ($this->connection->ping()) {
                return self::CONNECTION_OK;
            } else {
                return self::CONNECTION_KO;
            }
        }  catch (ConnectionException $e) {
            return self::CONNECTION_GONE;
        }
    }

    public function resetConnection()
    {
        try {
            $this->connection->close();
            $this->connection->connect();
        } catch (ConnectionException $e) {
            return false;
        }

        return true;
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
