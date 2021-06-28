<?php

declare(strict_types=1);

namespace QB;

use PDO;
use PDOStatement;
use QB\Generic\IQueryPart;
use QB\Generic\Statement\IUpdate;

class PDOHelper
{
    /**
     * @param PDO        $pdo
     * @param IQueryPart $query
     *
     * @return PDOStatement
     */
    public static function prepare(PDO $pdo, IQueryPart $query): PDOStatement
    {
        $sql    = (string)$query;
        $params = $query->getParams();
        $values = ($query instanceof IUpdate) ? $query->getValues() : [];

        $statement = $pdo->prepare($sql);
        foreach ($values as $k => $v) {
            $k2 = is_numeric($k) ? $k + 1 : $k;
            $statement->bindParam($k2, $v);
        }

        foreach ($params as $k => $v) {
            $k2 = is_numeric($k) ? $k + 1 + count($values) : $k;
            $statement->bindParam($k2, $v[0], $v[1]);
        }

        return $statement;
    }

    /**
     * @param PDO        $pdo
     * @param IQueryPart $query
     *
     * @return bool
     */
    public static function execute(PDO $pdo, IQueryPart $query): bool
    {
        $statement = static::prepare($pdo, $query);

        return $statement->execute();
    }

    /**
     * @param PDO        $pdo
     * @param IQueryPart $query
     * @param int        $mode
     * @param mixed      ...$args
     *
     * @return array
     */
    public static function fetchAll(PDO $pdo, IQueryPart $query, int $mode = PDO::FETCH_BOTH, ...$args): array
    {
        $statement = static::prepare($pdo, $query);

        $statement->execute();

        return $statement->fetchAll($mode, ...$args);
    }

    /**
     * @param PDO        $pdo
     * @param IQueryPart $query
     * @param int        $column
     *
     * @return array|int|bool|float|null
     */
    public static function fetchColumn(PDO $pdo, IQueryPart $query, int $column = 0)
    {
        $statement = static::prepare($pdo, $query);

        $statement->execute();

        return $statement->fetchColumn($column);
    }

    /**
     * @param PDO        $pdo
     * @param IQueryPart $query
     * @param int        $column
     * @param int        $orientation
     * @param int        $offset
     *
     * @return array
     */
    public static function fetch(
        PDO $pdo,
        IQueryPart $query,
        int $column = 0,
        int $orientation = PDO::FETCH_ORI_NEXT,
        int $offset = 0
    ): array {
        $statement = static::prepare($pdo, $query);

        $statement->execute();

        return $statement->fetch($column, $orientation, $offset);
    }
}
