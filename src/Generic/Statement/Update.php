<?php

declare(strict_types=1);

namespace QB\Generic\Statement;

use QB\Generic\Clause\Table;
use QB\Generic\Expr\Expr;
use QB\Generic\IQueryPart;

class Update implements IUpdate
{
    /** @var array<int,string|Table> */
    protected array $tables = [];

    /** @var string[] */
    protected array $modifiers = [];

    /** @var array<string,mixed> */
    protected array $values = [];

    /** @var IQueryPart[] */
    protected array $whereParts = [];

    /**
     * @param string|Table ...$tables
     *
     * @return $this
     */
    public function addFrom(string|Table ...$tables): static
    {
        $this->tables = array_merge($this->tables, $tables);

        return $this;
    }

    /**
     * @param string ...$modifiers
     *
     * @return $this
     */
    public function addModifier(string ...$modifiers): static
    {
        $this->modifiers = array_merge($this->modifiers, $modifiers);

        return $this;
    }

    /**
     * @param array<string,mixed> $values
     *
     * @return $this
     */
    public function setValues(array $values): static
    {
        $this->values = $values;

        return $this;
    }

    /**
     * @param string|IQueryPart ...$whereParts
     *
     * @return $this
     */
    public function addWhere(string|IQueryPart ...$whereParts): static
    {
        foreach ($whereParts as $wherePart) {
            $this->whereParts[] = is_string($wherePart) ? new Expr($wherePart) : $wherePart;
        }

        return $this;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        if (!$this->isValid()) {
            throw new \RuntimeException('Under-initialized UPDATE query. Table, values and where are necessary');
        }

        $sqlParts = array_merge(
            [$this->getCommand()],
            $this->getSet(),
            $this->getWhere(),
        );

        $sqlParts = array_filter($sqlParts);

        return implode(PHP_EOL, $sqlParts);
    }

    public function isValid(): bool
    {
        return count($this->tables) === 1 && count($this->values) > 0 && count($this->whereParts) > 0;
    }

    /**
     * @return string
     */
    protected function getCommand(): string
    {
        $sql   = [];
        $sql[] = 'UPDATE';
        $sql[] = implode(' ', $this->modifiers);
        $sql[] = $this->tables[0];

        $sql = array_filter($sql);

        return implode(' ', $sql);
    }

    /**
     * @return string[]
     */
    protected function getSet(): array
    {
        $values = [];
        foreach (array_keys($this->values) as $column) {
            $values[] = sprintf('%s = ?', $column);
        }

        return ['SET ' . implode(', ', $values)];
    }

    /**
     * @return string[]
     */
    protected function getWhere(): array
    {
        $whereParts = [];
        foreach ($this->whereParts as $wherePart) {
            $whereParts[] = (string)$wherePart;
        }

        return ['WHERE ' . implode(' AND ', $whereParts)];
    }

    /**
     * @return array
     */
    public function getParams(): array
    {
        $params = [];

        foreach ($this->whereParts as $wherePart) {
            $params = array_merge($params, $wherePart->getParams());
        }

        return $params;
    }

    /**
     * @return array
     */
    public function getValues(): array
    {
        return array_values($this->values);
    }
}
