<?php

namespace Tsuka\DB;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ConnectionException;
use Doctrine\DBAL\Query\QueryBuilder;

class DbalRepository
{
    const CONNECTION_OK = 'ok';
    const CONNECTION_KO = 'ko';
    const CONNECTION_GONE = 'gone';

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @return QueryBuilder
     */
    protected function getQueryBuilder()
    {
        if (!$this->connection->ping()) {
            $this->connection->close();
            $this->connection->connect();
        }

        return $this->connection->createQueryBuilder();
    }

    /**
     * @return string
     */
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

    /**
     * Generates a sql parameter array from data.
     *
     * Given a data array, this method generates an array with the same keys
     * replacing the values for sql parameter strings like this:
     *
     * <code>
     *  [
     *      'field' => ':field'
     *  ]
     * </code>
     *
     * That array is suitable to use directly with doctrine query builder.
     *
     * <code>
     *      $queryBuilder
     *          ->insert('table')
     *          ->values($this->parametrizeArray($data))
     *          ->setParameters($data);
     * </code>
     *
     * @param array $data
     * @return array
     */
    protected function parametrizeArray(array $data)
    {
        return array_combine(
            array_keys($data),
            array_map(function ($key) {
                return ":$key";
            }, array_keys($data))
        );
    }
}
