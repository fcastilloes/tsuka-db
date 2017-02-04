<?php

namespace Tsuka\DB;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Katana\Sdk\Action;

class DBService
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var string
     */
    private $table;

    /**
     * @var bool
     */
    private $isError = false;

    /**
     * @var array
     */
    private $entity = [];

    /**
     * @var array
     */
    private $simpleRelations = [];

    /**
     * @var array
     */
    private $multipleRelations = [];

    /**
     * @param Connection $connection
     * @param string $table
     * @param array $entity
     * @param array $simpleRelations
     * @param array $multipleRelations
     */
    public function __construct(
        Connection $connection,
        $table,
        array $entity = [],
        array $simpleRelations = [],
        array $multipleRelations = []
    ) {
        $this->connection = $connection;
        $this->table = $table;
        $this->entity = $entity;
        $this->simpleRelations = $simpleRelations;
        $this->multipleRelations = $multipleRelations;
    }

    /**
     * @param Action $action
     * @param array $filters
     * @return bool
     */
    public function list(Action $action, $filters = [])
    {
        $queryBuilder = $this->connection->createQueryBuilder();

        $queryBuilder
            ->select('*')
            ->from($this->table);

        foreach ($filters as $field => $value) {
            $queryBuilder
                ->andWhere("$field = :$field")
                ->setParameter(":$field", $value);
        }

        $stmt = $queryBuilder->execute();
        $action->setCollection($stmt->fetchAll());

        return true;
    }

    public function listByIds(Action $action, array $ids)
    {
        $queryBuilder = $this->connection->createQueryBuilder();

        $queryBuilder
            ->select('*')
            ->from($this->table)
            ->where('id IN (:ids)')
            ->setParameter('ids', $ids, Connection::PARAM_STR_ARRAY);

        $stmt = $queryBuilder->execute();
        $action->setCollection($stmt->fetchAll());

        return true;
    }

    /**
     * @param Action $action
     * @param string $id
     * @return bool
     */
    public function read(Action $action, $id)
    {
        $queryBuilder = $this->connection->createQueryBuilder();

        $queryBuilder
            ->select('*')
            ->from($this->table)
            ->where('id = :id')
            ->setParameter('id', $id);
        $stmt = $queryBuilder->execute();

        if ($entity = $stmt->fetch()) {
            $row = new DBRow($action, $entity);
            foreach ($this->simpleRelations as $relation) {
                $row->relateOne($relation);
            }
            foreach ($this->multipleRelations as $relation) {
                $row->relateMany($relation);
            }
            $row->resolveRelations();
            $action->setEntity($row->getEntity($this->entity));

            return true;

        } else {
            $action->error("Entity not found in $this->table", 1, '404 Not Found');

            return false;
        }
    }

    /**
     * @param Action $action
     * @param array $data
     * @param bool $setEntity
     * @return bool
     */
    public function create(Action $action, array $data, $setEntity = true)
    {
        $queryBuilder = $this->connection->createQueryBuilder();

        $queryBuilder
            ->insert($this->table);
        foreach ($data as $field => $value) {
            $queryBuilder
                ->setValue($field, ":$field")
                ->setParameter($field, $value);
        }

        try {
            $queryBuilder->execute();
            if ($setEntity) {
                $action->setEntity($data);
            }

            return true;

        } catch (UniqueConstraintViolationException $e) {
            $action->error("Item already exists in $this->table", 1, '409 Conflict');
        } catch (ForeignKeyConstraintViolationException $e) {
            $action->error("A relation was not found for $this->table", 1, '400 Bad Request');
        } catch (DBALException $e) {
            $action->error('PDO error: ' . $e->getMessage());
        }

        return false;
    }

    public function createFromView(Action $action, array $data, $view)
    {
        $queryBuilder = $this->connection->createQueryBuilder();

        $queryBuilder
            ->select(array_keys($data))
            ->from($view);

        foreach ($data as $field => $value) {
            $queryBuilder
                ->andWhere("$field = :$field");
        }

        // dbal does not support select insert
        $sql = "insert into $this->table {$queryBuilder->getSQL()}";
        $stmt = $this->connection->prepare($sql);

        try {
            $stmt->execute($data);

            if ($stmt->rowCount() === 0) {
                $action->error("Invalid relation", 1, '403 Forbidden');
                return false;
            }

            $action->setEntity($data);
            return true;

        } catch (UniqueConstraintViolationException $e) {
            $action->error("Item already exists in $this->table", 1, '409 Conflict');
        } catch (ForeignKeyConstraintViolationException $e) {
            $action->error("A relation was not found for $this->table", 1, '400 Bad Request');
        } catch (DBALException $e) {
            $action->error('PDO error: ' . $e->getMessage());
        }

        return false;
    }

    /**
     * @param Action $action
     * @param array $filters
     * @return bool
     */
    public function delete(Action $action, array $filters)
    {
        $queryBuilder = $this->connection->createQueryBuilder();

        $queryBuilder
            ->delete($this->table);

        foreach ($filters as $field => $value) {
            $queryBuilder
                ->andWhere("$field = :$field")
                ->setParameter(":$field", $value);
        }

        try {
            $stmt = $queryBuilder->execute();

            if ($stmt === 0) {
                $action->error("Item not found in $this->table", 1, '404 Not Found');
                return false;
            } else {
                return true;
            }
        } catch (DBALException $e) {
            $action->error('PDO error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * @return bool
     */
    public function isError()
    {
        return $this->isError;
    }

}
