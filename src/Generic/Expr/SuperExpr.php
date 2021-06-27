<?php

declare(strict_types=1);

namespace QB\Generic\Expr;

use PDO;
use QB\Generic\IQueryPart;

class SuperExpr extends Expr
{
    protected const PDO_TYPES = [PDO::PARAM_INT, PDO::PARAM_STR, PDO::PARAM_BOOL, PDO::PARAM_NULL];

    /**
     * SuperExpr constructor.
     *
     * @param string|IQueryPart $sql
     * @param array             $params
     * @param string            $super
     */
    public function __construct(string|IQueryPart $sql, array $params = [], string $super = '??')
    {
        if ($sql instanceof IQueryPart) {
            $params = array_merge($params, $sql->getParams());
            $sql    = (string)$sql;
        }

        $splitSql = explode($super, $sql);

        $sql = (string)array_shift($splitSql);

        $flatParams = [];
        foreach ($params as $i => $p) {
            if (!$this->isParamArray($p)) {
                $flatParams[] = $p;
                $sql        .= '?';
            } else {
                $flatParams = array_merge($flatParams, $p);
                $sql        .= str_repeat('?, ', count($p) - 1) . '?';
            }

            if (isset($splitSql[$i])) {
                $sql .= $splitSql[$i];
            }
        }

        parent::__construct($sql, $flatParams);
    }

    /**
     * @param bool|int|float|string|array|null $param
     *
     * @return bool
     */
    protected function isParamArray(bool|int|float|string|array|null $param): bool
    {
        if (!is_array($param)) {
            return false;
        }

        if (count($param) != 2) {
            return true;
        }

        if (!array_key_exists(1, $param)) {
            return true;
        }

        return !in_array($param[1], static::PDO_TYPES, true);
    }
}
