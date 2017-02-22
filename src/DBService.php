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
     * @param array $filters
     * @return array
     */
    public function list($filters = [])
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

        return $stmt->fetchAll();
    }

    /**
     * @param array $ids
     * @return array
     */
    public function listByIds(array $ids)
    {
        $queryBuilder = $this->connection->createQueryBuilder();

        $queryBuilder
            ->select('*')
            ->from($this->table)
            ->where('id IN (:ids)')
            ->setParameter('ids', $ids, Connection::PARAM_STR_ARRAY);

        $stmt = $queryBuilder->execute();

        return $stmt->fetchAll();
    }

    /**
     * @param string $id
     * @return mixed
     */
    public function read($id)
    {
        $queryBuilder = $this->connection->createQueryBuilder();

        $queryBuilder
            ->select('*')
            ->from($this->table)
            ->where('id = :id')
            ->setParameter('id', $id);
        $stmt = $queryBuilder->execute();

        return $stmt->fetch();
    }

    /**
     * @param array $filters
     * @return bool
     */
    public function first($filters = [])
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

        return $stmt->fetch();
    }

    /**
     * @param array $data
     * @param bool $setEntity
     * @return bool
     */
    public function create(array $data, $setEntity = true)
    {
        $queryBuilder = $this->connection->createQueryBuilder();

        $queryBuilder
            ->insert($this->table);
        foreach ($data as $field => $value) {
            $queryBuilder
                ->setValue($field, ":$field")
                ->setParameter($field, $value);
        }

        $queryBuilder->execute();

        return true;
    }

    /**
     * @param array $filters
     * @return bool
     */
    public function delete(array $filters)
    {
        $queryBuilder = $this->connection->createQueryBuilder();

        $queryBuilder
            ->delete($this->table);

        foreach ($filters as $field => $value) {
            $queryBuilder
                ->andWhere("$field = :$field")
                ->setParameter(":$field", $value);
        }

        return $queryBuilder->execute();
    }

	/**
	 * @param array $data
	 * @param array $filters
	 * @return bool
	 */
	public function update(array $data, array $filters)
	{
		$queryBuilder = $this->connection->createQueryBuilder();

		$queryBuilder
			->update($this->table);


		foreach ($data as $field => $value) {
			$queryBuilder
				->set($field, ":$field")
				->setParameter($field, $value);
		}

		foreach ($filters as $field => $value) {
			$queryBuilder
				->andWhere("$field = :$field")
				->setParameter(":$field", $value);
		}

		return $queryBuilder->execute();
	}

    /**
     * @return bool
     */
    public function isError()
    {
        return $this->isError;
    }
}
