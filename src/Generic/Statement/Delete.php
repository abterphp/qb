<?php

declare(strict_types=1);

namespace QB\Generic\Statement;

use QB\Generic\Clause\Table;
use QB\Generic\Expr\Expr;
use QB\Generic\IQueryPart;

class Delete implements IDelete
{
    /** @var array<int,string|Table> */
    protected array $tables = [];

    /** @var string[] */
    protected array $modifiers = [];

    /** @var IQueryPart[] */
    protected array $whereParts = [];

    /**
     * @param string|Table ...$tables
     *
     * @return $this
     */
    public function from(string|Table ...$tables): static
    {
        $this->tables = array_merge($this->tables, $tables);

        return $this;
    }

    /**
     * @param string|IQueryPart ...$whereParts
     *
     * @return $this
     */
    public function where(string|IQueryPart ...$whereParts): static
    {
        foreach ($whereParts as $wherePart) {
            $wherePart = is_string($wherePart) ? new Expr($wherePart) : $wherePart;

            $this->whereParts[] = $wherePart;
        }

        return $this;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        if (!$this->isValid()) {
            throw new \RuntimeException('Under-initialized DELETE query. Table and where are necessary');
        }

        $delete = $this->delete();

        $sqlParts = array_merge(
            [$delete],
            $this->getWhere(),
        );

        $sqlParts = array_filter($sqlParts);

        return implode(PHP_EOL, $sqlParts);
    }

    public function isValid(): bool
    {
        return count($this->tables) === 1 || count($this->whereParts) > 0;
    }

    protected function delete(): string
    {
        $sql = [];
        $sql[] = 'DELETE';
        $sql[] = $this->getModifiers();
        $sql[] = 'FROM';
        $sql[] = $this->tables[0];

        $sql = array_filter($sql);

        return implode(' ', $sql);
    }

    protected function getModifiers(): string
    {
        if (empty($this->modifiers)) {
            return '';
        }

        return implode(' ', $this->modifiers);
    }

    protected function getWhere(): array
    {
        if (count($this->whereParts) === 0) {
            return [];
        }

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
}
