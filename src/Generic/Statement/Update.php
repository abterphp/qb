<?php

declare(strict_types=1);

namespace QB\Generic\Statement;

use QB\Generic\Clause\Table;
use QB\Generic\Expr\Expr;
use QB\Generic\IQueryPart;
use RuntimeException;

class Update implements IUpdate
{
    /** @var array<int,Table|string> */
    protected array $tables = [];

    /** @var string[] */
    protected array $modifiers = [];

    /** @var array<string,mixed> */
    protected array $rawValues = [];

    /** @var IQueryPart[] */
    protected array $whereParts = [];

    /**
     * @param Table|string ...$tables
     */
    public function __construct(Table|string ...$tables)
    {
        $this->tables = array_merge($this->tables, $tables);
    }

    /**
     * @param string ...$modifiers
     *
     * @return $this
     */
    public function modifier(string ...$modifiers): static
    {
        $this->modifiers = array_merge($this->modifiers, $modifiers);

        return $this;
    }

    /**
     * @param array<string,mixed> $values
     *
     * @return $this
     */
    public function values(array $values): static
    {
        $this->rawValues = $values;

        return $this;
    }

    /**
     * @param IQueryPart|string ...$whereParts
     *
     * @return $this
     */
    public function where(IQueryPart|string ...$whereParts): static
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
            throw new RuntimeException('Under-initialized UPDATE query. Table, values and where are necessary');
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
        return count($this->tables) === 1 && count($this->rawValues) > 0 && count($this->whereParts) > 0;
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
        foreach ($this->rawValues as $column => $value) {
            $values[] = sprintf('%s = %s', $column, $value);
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

        foreach ($this->rawValues as $values) {
            if ($values instanceof IQueryPart) {
                $params = array_merge($params, $values->getParams());
            }
        }

        foreach ($this->whereParts as $wherePart) {
            $params = array_merge($params, $wherePart->getParams());
        }

        return $params;
    }
}
