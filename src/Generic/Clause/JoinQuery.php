<?php

declare(strict_types=1);

namespace QB\Generic\Clause;

use QB\Generic\IQueryPart;
use QB\Generic\Statement\Select;

class JoinQuery implements IJoin
{
    public string $type;
    public Select $subQuery;
    public IQueryPart $on;
    public string $alias;

    /**
     * JoinQuery constructor.
     *
     * @param string     $type
     * @param Select     $subQuery
     * @param IQueryPart $on
     * @param string     $alias
     */
    public function __construct(string $type, Select $subQuery, IQueryPart $on, string $alias)
    {
        $this->type     = $type;
        $this->subQuery = $subQuery;
        $this->on       = $on;
        $this->alias    = $alias;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        $sql = str_replace("\n", ' ', $this->subQuery->__toString());

        return sprintf('%s (%s) AS %s ON %s', $this->type, $sql, $this->alias, $this->on->__toString());
    }

    /**
     * @return array
     */
    public function getParams(): array
    {
        return array_merge($this->subQuery->getParams(), $this->on->getParams());
    }
}
