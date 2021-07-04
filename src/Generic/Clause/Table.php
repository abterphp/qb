<?php

declare(strict_types=1);

namespace QB\Generic\Clause;

class Table implements ITable
{
    protected string $tableName;

    protected ?string $alias = null;

    /**
     * Table constructor.
     *
     * @param string      $tableName table name
     * @param string|null $alias
     */
    public function __construct(string $tableName, ?string $alias = null)
    {
        $this->tableName = $tableName;
        $this->alias     = $alias;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        if ($this->alias === null) {
            return $this->tableName;
        }

        return sprintf('%s AS %s', $this->tableName, $this->alias);
    }

    /**
     * @return array
     */
    public function getParams(): array
    {
        return [];
    }
}
