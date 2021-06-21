<?php

declare(strict_types=1);

namespace QB\Generic\Clause;

use QB\Generic\IQueryPart;
use QB\Generic\Statement\Select;

class Column implements IColumn
{
    protected string|IQueryPart $expr;

    protected ?string $alias = null;

    /**
     * Expr constructor.
     *
     * @param string|IQueryPart $expr column name or expression to be used
     * @param string|null       $alias
     */
    public function __construct(string|IQueryPart $expr, ?string $alias = null)
    {
        $this->expr  = $expr;
        $this->alias = $alias;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        $expr = is_string($this->expr) ? $this->expr : (string)$this->expr;

        if ($this->expr instanceof Select) {
            $expr = '(' . str_replace(PHP_EOL, ' ', $expr) . ')';
        }

        if ($this->alias === null) {
            return $expr;
        }

        return sprintf('%s AS %s', $expr, $this->alias);
    }

    /**
     * @return array
     */
    public function getParams(): array
    {
        if (is_string($this->expr)) {
            return [];
        }

        return $this->expr->getParams();
    }
}
