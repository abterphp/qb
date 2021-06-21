<?php

declare(strict_types=1);

namespace QB\Generic\Clause;

use QB\Generic\IQueryPart;

interface IJoin extends IQueryPart
{
    public const TYPE_INNER_JOIN = 'INNER JOIN';
    public const TYPE_LEFT_JOIN  = 'LEFT JOIN';
    public const TYPE_RIGHT_JOIN = 'RIGHT JOIN';
    public const TYPE_FULL_JOIN  = 'FULL JOIN';

    public const VALID_TYPES = [
        self::TYPE_INNER_JOIN,
        self::TYPE_LEFT_JOIN,
        self::TYPE_RIGHT_JOIN,
        self::TYPE_FULL_JOIN,
    ];
}
