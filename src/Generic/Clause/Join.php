<?php

declare(strict_types=1);

namespace QB\Generic\Clause;

use InvalidArgumentException;
use QB\Generic\IQueryPart;

class Join implements IJoin
{
    public string $type;
    public ITable|string $table;
    public IQueryPart|string|null $on;

    /**
     * Join constructor.
     *
     * @param string                 $type
     * @param ITable|string          $table
     * @param IQueryPart|string|null $on
     */
    public function __construct(string $type, ITable|string $table, IQueryPart|string|null $on)
    {
        if (!in_array($type, IJoin::VALID_TYPES)) {
            throw new InvalidArgumentException(sprintf('invalid join type: %s', $type));
        }

        $this->type  = $type;
        $this->table = $table;
        $this->on    = $on;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        if ($this->on) {
            return sprintf('%s %s ON %s', $this->type, (string)$this->table, (string)$this->on);
        }

        return sprintf('%s %s', $this->type, (string)$this->table);
    }

    /**
     * @return array
     */
    public function getParams(): array
    {
        $params = [];
        if ($this->table instanceof ITable) {
            $params = array_merge($params, $this->table->getParams());
        }
        if ($this->on instanceof IQueryPart) {
            $params = array_merge($params, $this->on->getParams());
        }

        return $params;
    }
}
