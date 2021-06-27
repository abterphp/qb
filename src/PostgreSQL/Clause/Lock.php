<?php

declare(strict_types=1);

namespace QB\PostgreSQL\Clause;

use QB\MySQL\Clause\Lock as MySQLLock;

class Lock extends MySQLLock
{
    public const FOR_NO_KEY_UPDATE = 'NO KEY UPDATE';
    public const FOR_KEY_SHARE     = 'KEY SHARE';

    protected const VALID_FOR = [self::FOR_UPDATE, self::FOR_SHARE, self::FOR_NO_KEY_UPDATE, self::FOR_KEY_SHARE];
}
