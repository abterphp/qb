<?php

declare(strict_types=1);

namespace QB\MySQL\Clause;

use QB\Generic\IQueryPart;

class CombiningQuery
{
    public const TYPE_UNION = 'UNION';

    public const MODIFIER_ALL      = 'ALL';
    public const MODIFIER_DISTINCT = 'DISTINCT';

    protected const VALID_TYPE      = [null, self::TYPE_UNION];
    protected const VALID_MODIFIERS = [null, self::MODIFIER_ALL, self::MODIFIER_DISTINCT];

    protected string $type;

    protected IQueryPart $queryPart;

    protected ?string $modifier = null;

    /**
     * CombiningQuery constructor.
     *
     * @param string      $type
     * @param IQueryPart  $queryPart
     * @param string|null $modifier
     */
    public function __construct(string $type, IQueryPart $queryPart, ?string $modifier = null)
    {
        if (!$this->isValid($type, $modifier)) {
            $data = print_r([$type, $modifier], true) ?? 'print_r failure';
            throw new \InvalidArgumentException(
                sprintf('invalid arguments for %s. arguments: %s', __CLASS__, $data)
            );
        }

        $this->type      = $type;
        $this->queryPart = $queryPart;
        $this->modifier  = $modifier;
    }

    /**
     * @param string      $type
     * @param string|null $modifier
     *
     * @return bool
     */
    private function isValid(string $type, ?string $modifier = null): bool
    {
        if (!in_array($type, static::VALID_TYPE, true)) {
            return false;
        }

        if (!in_array($modifier, static::VALID_MODIFIERS, true)) {
            return false;
        }

        return true;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        $modifier = '';
        if ($this->modifier) {
            $modifier = ' ' . $this->modifier;
        }

        $parts   = [];
        $parts[] = $this->type . $modifier;
        $parts[] = (string)$this->queryPart;

        return implode(PHP_EOL, $parts);
    }
}
