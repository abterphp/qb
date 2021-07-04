<?php

declare(strict_types=1);

namespace QB\MySQL\Statement;

use QB\Generic\Statement\Update as GenericUpdate;

class Update extends GenericUpdate
{
    public const LOW_PRIORITY  = 'LOW_PRIORITY';
    public const IGNORE        = 'IGNORE';

    /** @var array<int,string> */
    protected array $modifiers = [];

    protected ?int $limit = null;

    /**
     * @param string ...$modifiers
     *
     * @return $this
     */
    public function modifier(string ...$modifiers): static
    {
        foreach ($modifiers as $modifier) {
            switch ($modifier) {
                case static::LOW_PRIORITY:
                    $this->modifiers[0] = $modifier;
                    break;
                case static::IGNORE:
                    $this->modifiers[1] = $modifier;
                    break;
            }
        }

        return $this;
    }

    /**
     * @param int|null $limit
     *
     * @return $this
     */
    public function setLimit(?int $limit): static
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        $parts = array_merge(
            [parent::__toString()],
            $this->getLimit(),
        );

        return implode(PHP_EOL, $parts);
    }

    protected function getLimit(): array
    {
        $parts = [];
        if ($this->limit !== null) {
            $parts[] = 'LIMIT ' . $this->limit;
        }

        return $parts;
    }
}
