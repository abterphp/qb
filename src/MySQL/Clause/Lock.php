<?php

declare(strict_types=1);

namespace QB\MySQL\Clause;

class Lock
{
    public const LOCK_IN_SHARE_MODE = 'LOCK IN SHARE MODE';

    public const FOR_UPDATE = 'UPDATE';
    public const FOR_SHARE  = 'SHARE';

    public const MODIFIER_NOWAIT      = 'NOWAIT';
    public const MODIFIER_SKIP_LOCKED = 'SKIP LOCKED';

    protected const VALID_FOR       = [null, self::LOCK_IN_SHARE_MODE, self::FOR_UPDATE, self::FOR_SHARE];
    protected const VALID_MODIFIERS = [null, self::MODIFIER_NOWAIT, self::MODIFIER_SKIP_LOCKED];

    protected string $for = self::LOCK_IN_SHARE_MODE;

    /** @var string[] */
    protected array $tables = [];

    protected ?string $modifier = null;

    /**
     * Lock constructor.
     *
     * @param string|null $for
     * @param string[]    $tables
     * @param string|null $modifier
     */
    public function __construct(?string $for = null, array $tables = [], ?string $modifier = null)
    {
        if (!$this->isValid($for, $modifier)) {
            throw new \InvalidArgumentException(
                sprintf('invalid arguments for %s. arguments: %s', __CLASS__, print_r(func_get_args(), true))
            );
        }

        if ($for === null) {
            return;
        }

        $this->for      = $for;
        $this->tables   = $tables;
        $this->modifier = $modifier;
    }

    /**
     * @param string|null $for
     * @param string|null $modifier
     *
     * @return bool
     */
    private function isValid(?string $for = null, ?string $modifier = null): bool
    {
        if (!in_array($for, static::VALID_FOR, true)) {
            return false;
        }

        if (!in_array($modifier, static::VALID_MODIFIERS, true)) {
            return false;
        }

        return true;
    }

    public function __toString(): string
    {
        if ($this->for === self::LOCK_IN_SHARE_MODE) {
            return self::LOCK_IN_SHARE_MODE;
        }

        $parts   = [];
        $parts[] = 'FOR ' . $this->for;
        if (count($this->tables) > 0) {
            $parts[] = 'OF ' . implode(', ', $this->tables);
        }
        if ($this->modifier) {
            $parts[] = $this->modifier;
        }

        return implode(' ', $parts);
    }
}
