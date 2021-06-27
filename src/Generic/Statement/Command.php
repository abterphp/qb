<?php

declare(strict_types=1);

namespace QB\Generic\Statement;

use QB\Generic\IQueryPart;

class Command implements IQueryPart
{
    protected string $sql;

    /** @var IQueryPart[] */
    protected array $queryParts;

    /**
     * Command constructor.
     *
     * @param string     $sql
     * @param IQueryPart ...$expressions
     */
    public function __construct(string $sql, IQueryPart ...$expressions)
    {
        $this->sql        = $sql;
        $this->queryParts = $expressions;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        $parts = [];
        foreach ($this->queryParts as $expr) {
            $parts[] = (string)$expr;
        }

        return sprintf($this->sql, ...$parts);
    }

    /**
     * @return array
     */
    public function getParams(): array
    {
        $params = [];

        foreach ($this->queryParts as $expr) {
            $params = array_merge($params, $expr->getParams());
        }

        return $params;
    }
}
