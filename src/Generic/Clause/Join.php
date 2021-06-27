<?php

declare(strict_types=1);

namespace QB\Generic\Clause;

use QB\Generic\IQueryPart;

class Join implements IJoin
{
    public string $type;
    public string $tableName;
    public IQueryPart $on;
    public ?string $alias;

    /**
     * Join constructor.
     *
     * @param string            $type
     * @param string            $tableName
     * @param string|IQueryPart $on
     * @param string|null       $alias
     */
    public function __construct(string $type, string $tableName, string|IQueryPart $on, ?string $alias = null)
    {
        if (!in_array($type, IJoin::VALID_TYPES)) {
            throw new \InvalidArgumentException(sprintf('Invalid join type: %s', $type));
        }

        $this->type      = $type;
        $this->tableName = $tableName;
        $this->on        = $on;
        $this->alias     = $alias;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        if ($this->alias) {
            return sprintf('%s %s AS %s ON %s', $this->type, $this->tableName, $this->alias, (string)$this->on);
        }

        return sprintf('%s %s ON %s', $this->type, $this->tableName, (string)$this->on);
    }

    /**
     * @return array
     */
    public function getParams(): array
    {
        return $this->on->getParams();
    }
}
