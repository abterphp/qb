<?php

declare(strict_types=1);

namespace QB\Generic\Expr;

use PDO;
use QB\Generic\IQueryPart;

class Expr implements IQueryPart
{
    /** @var string|IQueryPart */
    protected $sql;

    protected int $unnamedParamCount;

    /** @var array<int,array<int,mixed>> */
    protected array $unnamedParams = [];

    /** @var array<string,array<int,mixed>> */
    protected array $namedParams = [];

    /**
     * Expr constructor.
     *
     * @param string|IQueryPart $sql
     * @param array             $params
     */
    public function __construct(string|IQueryPart $sql, array $params = [])
    {
        $this->sql = $sql;

        $this->unnamedParamCount = substr_count($sql, '?');

        $this->bindParams($params);
    }

    /**
     * @param string   $param
     * @param          $var
     * @param int      $type
     */
    public function bindNamedParam(string $param, &$var, int $type = PDO::PARAM_STR)
    {
        $sql = (string)$this->sql;

        if (!str_contains($sql, $param)) {
            throw new \InvalidArgumentException(
                sprintf('Named param was not expected. Param: %s, SQL: %s', $param, $sql)
            );
        }

        $this->namedParams[$param] = [$var, $type];
    }

    /**
     * @param     $var
     * @param int $type
     */
    public function bindParam(&$var, int $type = PDO::PARAM_STR)
    {
        if ($this->unnamedParamCount <= count($this->unnamedParams)) {
            throw new \LogicException(sprintf('More unnamed params than expected for SQL: %s', $this->sql));
        }

        $this->unnamedParams[] = [$var, $type];
    }

    /**
     * @param array $params
     */
    public function bindParams(array $params)
    {
        foreach ($params as $k => $param) {
            if (is_string($k)) {
                if (is_scalar($param)) {
                    $this->bindNamedParam($k, $param);
                } else {
                    $this->bindNamedParam($k, $param[0], $param[1]);
                }
            } else {
                if (is_scalar($param)) {
                    $this->bindParam($param);
                } else {
                    $this->bindParam($param[0], $param[1]);
                }
            }
        }
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return (string)$this->sql;
    }

    /**
     * @return array
     */
    public function getParams(): array
    {
        return array_merge($this->unnamedParams, $this->namedParams);
    }
}
