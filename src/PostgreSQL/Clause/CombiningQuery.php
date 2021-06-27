<?php

declare(strict_types=1);

namespace QB\PostgreSQL\Clause;

use QB\MySQL\Clause\CombiningQuery as MySQLCombiningQuery;

class CombiningQuery extends MySQLCombiningQuery
{
    public const TYPE_INTERSECT = 'INTERSECT';
    public const TYPE_EXCEPT    = 'EXCEPT';

    protected const VALID_TYPE      = [null, self::TYPE_UNION, self::TYPE_INTERSECT, self::TYPE_EXCEPT];
    protected const VALID_MODIFIERS = [null, self::MODIFIER_ALL];
}
