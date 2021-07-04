<?php

declare(strict_types=1);

namespace QB\Generic\Clause;

use QB\Generic\IQueryPart;

class QueryAsTable implements ITable
{
    protected IQueryPart|string $query;

    protected string $alias;

    /**
     * TableQuery constructor.
     *
     * @param IQueryPart|string $query
     * @param string            $alias
     */
    public function __construct(IQueryPart|string $query, string $alias)
    {
        $this->query = $query;
        $this->alias = $alias;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return sprintf('(%s) AS %s', (string)$this->query, $this->alias);
    }

    /**
     * @return array
     */
    public function getParams(): array
    {
        if ($this->query instanceof IQueryPart) {
            return $this->query->getParams();
        }

        return [];
    }
}
