<?php

declare(strict_types=1);

namespace QB\MySQL\Statement;

use QB\Generic\IQueryPart;
use QB\Generic\Statement\Insert as GenericInsert;

class Insert extends GenericInsert
{
    public const LOW_PRIORITY  = 'LOW_PRIORITY';
    public const HIGH_PRIORITY = 'HIGH_PRIORITY';
    public const DELAYED       = 'DELAYED';
    public const IGNORE        = 'IGNORE';

    /** @var array<int,string> */
    protected array $modifiers = [];

    protected ?IQueryPart $onDuplicateKeyUpdate = null;

    protected ?Select $select = null;

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
                case static::DELAYED:
                case static::HIGH_PRIORITY:
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
     * @param IQueryPart $onDuplicateKeyUpdate
     *
     * @return $this
     */
    public function setOnDuplicateKeyUpdate(IQueryPart $onDuplicateKeyUpdate): static
    {
        $this->onDuplicateKeyUpdate = $onDuplicateKeyUpdate;

        return $this;
    }

    /**
     * @param Select $select
     *
     * @return $this
     */
    public function select(Select $select): static
    {
        $this->select = $select;

        return $this;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        $parts = [parent::__toString()];

        if ($this->onDuplicateKeyUpdate) {
            $parts[] = sprintf('ON DUPLICATE KEY UPDATE %s', (string)$this->onDuplicateKeyUpdate);
        }

        return implode(PHP_EOL, $parts);
    }

    /**
     * @return bool
     */
    public function isValid(): bool
    {
        return !empty($this->table) && (count($this->rawValues) > 0 || $this->select !== null);
    }

    /**
     * @return string[]
     */
    protected function getRawValues(): array
    {
        if ($this->select !== null) {
            return [(string)$this->select];
        }

        return parent::getRawValues();
    }
}
