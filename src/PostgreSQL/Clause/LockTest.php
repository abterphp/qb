<?php

declare(strict_types=1);

namespace QB\PostgreSQL\Clause;

use PHPUnit\Framework\TestCase;

class LockTest extends TestCase
{
    /**
     * @return array[]
     */
    public function toStringProvider(): array
    {
        return [
            [Lock::FOR_UPDATE, [], null, 'FOR UPDATE'],
            [Lock::FOR_SHARE, [], null, 'FOR SHARE'],
            [Lock::FOR_NO_KEY_UPDATE, [], null, 'FOR NO KEY UPDATE'],
            [Lock::FOR_KEY_SHARE, [], null, 'FOR KEY SHARE'],
            [Lock::FOR_UPDATE, [], Lock::MODIFIER_NOWAIT, "FOR UPDATE NOWAIT"],
            [Lock::FOR_SHARE, [], Lock::MODIFIER_SKIP_LOCKED, "FOR SHARE SKIP LOCKED"],
            [Lock::FOR_UPDATE, ['foo', 'bar'], null, "FOR UPDATE OF foo, bar"],
            [Lock::FOR_SHARE, ['foo', 'bar'], Lock::MODIFIER_NOWAIT, "FOR SHARE OF foo, bar NOWAIT"],
        ];
    }

    /**
     * @dataProvider toStringProvider
     *
     * @param string|null $for
     * @param array       $tables
     * @param string|null $modifier
     * @param string      $expectedSql
     */
    public function testToString(?string $for, array $tables, ?string $modifier, string $expectedSql)
    {
        $lock = new Lock($for, $tables, $modifier);
        $sql  = (string)$lock;

        $this->assertSame($expectedSql, $sql);
    }

    /**
     * @return array[]
     */
    public function constructValidationProvider(): array
    {
        return [
            'lock in share more invalid' => [Lock::LOCK_IN_SHARE_MODE, [], null],
            'invalid for'                => ['foo', [], null],
            'invalid modifier'           => [Lock::FOR_SHARE, [], 'foo'],
        ];
    }

    /**
     * @dataProvider constructValidationProvider
     * @suppress     PhanNoopNew
     *
     * @param string|null $for
     * @param array       $tables
     * @param string|null $modifier
     */
    public function testConstructValidation(?string $for, array $tables, ?string $modifier)
    {
        $this->expectException(\InvalidArgumentException::class);

        new Lock($for, $tables, $modifier);
    }
}
