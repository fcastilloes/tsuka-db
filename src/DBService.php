<?php

namespace Tsuka\DB;

use Entity;
use Katana\Sdk\Action;
use PDO;
use PDOException;

class DBService
{
    /**
     * @var PDO
     */
    private $pdo;

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
     * @param PDO $pdo
     * @param string $table
     * @param array $entity
     * @param array $simpleRelations
     * @param array $multipleRelations
     */
    public function __construct(
        PDO $pdo,
        $table,
        array $entity = [],
        array $simpleRelations = [],
        array $multipleRelations = []
    ) {
        $this->pdo = $pdo;
        $this->table = $table;
        $this->entity = $entity;
        $this->simpleRelations = $simpleRelations;
        $this->multipleRelations = $multipleRelations;
    }

    /**
     * @param string $field
     * @return string
     */
    private function getPlaceholder($field)
    {
        return ":$field";
    }

    /**
     * @param Action $action
     * @return bool
     */
    public function list(Action $action)
    {
        $result = $this->pdo->query("select * from $this->table");
        $action->setCollection($result->fetchAll());

        return true;
    }

    public function listByIds(Action $action, array $ids)
    {
        $placeholders = array_map(function ($i) {
            return ":id$i";
        }, range(0, count($ids) -1));

        $keys = array_map(function ($i) {
            return "id{$i}";
        }, range(0, count($ids) -1));

        $queryString = implode(', ', $placeholders);
        $parameters = array_combine($keys, $ids);

        $stmt = $this->pdo->prepare("select * from $this->table WHERE id IN ($queryString)");
        $stmt->execute($parameters);
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
        $stmt = $this->pdo->prepare("select * from $this->table where id = :id");
        $stmt->execute(['id' => $id]);

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
        $fields = array_keys($data);
        $fieldList = implode(', ', $fields);
        $placeholders = implode(', ', array_map([$this, 'getPlaceholder'], $fields));

        $stmt = $this->pdo->prepare("INSERT INTO $this->table ($fieldList) VALUES ($placeholders)");

        try {
            $stmt->execute($data);
            if ($setEntity) {
                $action->setEntity($data);
            }

            return true;

        } catch (PDOException $e) {
            if ($stmt->errorInfo()[1] === 1062) {
                $action->error("Item already exists in $this->table", 1, '409 Conflict');
            } elseif ($stmt->errorInfo()[1] === 1452) {
                $action->error("A relation was not found for $this->table", 1, '400 Bad Request');
            } else {
                $action->error('PDO error: ' . $e->getMessage());
            }

            return false;
        }
    }

    /**
     * @param string $field
     * @return string
     */
    private function getEquality($field)
    {
        return "$field = :$field";
    }

    /**
     * @param Action $action
     * @param array $data
     * @return bool
     */
    public function delete(Action $action, array $data)
    {
        $equalities = implode(' AND ', array_map([$this, 'getEquality'], array_keys($data)));

        $stmt = $this->pdo->prepare("DELETE FROM $this->table WHERE $equalities");

        try {
            $stmt->execute($data);

            if ($stmt->rowCount() === 0) {
                $action->error("Item not found in $this->table", 1, '404 Not Found');
                return false;
            } else {
                return true;
            }
        } catch (PDOException $e) {
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
