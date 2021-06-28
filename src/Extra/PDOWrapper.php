<?php

declare(strict_types=1);

namespace QB\Extra;

use PDO;
use PDOStatement;
use QB\Generic\IQueryPart;

class PDOWrapper
{
    protected PDO $pdo;

    /**
     * Wrapper constructor.
     *
     * @param \PDO $pdo
     */
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * @return \PDO
     */
    public function getPdo(): PDO
    {
        return $this->pdo;
    }

    /**
     * @suppress PhanUndeclaredMethod
     *
     * @param IQueryPart $query
     *
     * @return PDOStatement
     */
    public function prepare(IQueryPart $query): PDOStatement
    {
        $sql    = (string)$query;
        $params = $query->getParams();

        $statement = $this->pdo->prepare($sql);

        foreach ($params as $k => $v) {
            $k2 = is_numeric($k) ? $k + 1 : $k;
            $statement->bindParam($k2, $v[0], $v[1]);
        }

        return $statement;
    }

    /**
     * @param IQueryPart $query
     *
     * @return bool
     */
    public function execute(IQueryPart $query): bool
    {
        $statement = $this->prepare($query);

        return $statement->execute();
    }

    /**
     * @param IQueryPart $query
     * @param int        $mode
     * @param mixed      ...$args
     *
     * @return array
     */
    public function fetchAll(IQueryPart $query, int $mode = PDO::FETCH_BOTH, ...$args): array
    {
        $statement = $this->prepare($query);

        $statement->execute();

        return $statement->fetchAll($mode, ...$args);
    }

    /**
     * @param IQueryPart $query
     * @param int        $column
     *
     * @return array|int|bool|float|null
     */
    public function fetchColumn(IQueryPart $query, int $column = 0)
    {
        $statement = $this->prepare($query);

        $statement->execute();

        return $statement->fetchColumn($column);
    }

    /**
     * @param IQueryPart $query
     * @param int        $column
     * @param int        $orientation
     * @param int        $offset
     *
     * @return array
     */
    public function fetch(
        IQueryPart $query,
        int $column = 0,
        int $orientation = PDO::FETCH_ORI_NEXT,
        int $offset = 0
    ): array {
        $statement = $this->prepare($query);

        $statement->execute();

        return $statement->fetch($column, $orientation, $offset);
    }
}
