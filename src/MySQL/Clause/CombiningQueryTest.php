<?php

declare(strict_types=1);

namespace QB\MySQL\Clause;

use PHPUnit\Framework\TestCase;
use QB\Generic\IQueryPart;
use QB\MySQL\Statement\Select;

class CombiningQueryTest extends TestCase
{
    protected IQueryPart $queryPart;

    public function setUp(): void
    {
        $this->queryPart = (new Select())->addColumn('2');
    }

    /**
     * @return array[]
     */
    public function toStringProvider(): array
    {
        return [
            [CombiningQuery::TYPE_UNION, null, "UNION\nSELECT 2"],
            [CombiningQuery::TYPE_UNION, CombiningQuery::MODIFIER_DISTINCT, "UNION DISTINCT\nSELECT 2"],
            [CombiningQuery::TYPE_UNION, CombiningQuery::MODIFIER_ALL, "UNION ALL\nSELECT 2"],
        ];
    }

    /**
     * @dataProvider toStringProvider
     *
     * @param string      $type
     * @param string|null $modifier
     * @param string      $expectedSql
     */
    public function testToString(string $type, ?string $modifier, string $expectedSql)
    {
        $lock = new CombiningQuery($type, $this->queryPart, $modifier);
        $sql  = (string)$lock;

        $this->assertSame($expectedSql, $sql);
    }

    /**
     * @return array[]
     */
    public function constructValidationProvider(): array
    {
        return [
            'invalid type'     => ['foo', null],
            'invalid modifier' => [CombiningQuery::TYPE_UNION, 'foo'],
        ];
    }

    /**
     * @dataProvider constructValidationProvider
     *
     * @suppress     PhanNoopNew
     *
     * @param string|null $type
     * @param string|null $modifier
     */
    public function testConstructValidation(?string $type, ?string $modifier)
    {
        $this->expectException(\InvalidArgumentException::class);

        new CombiningQuery($type, $this->queryPart, $modifier);
    }
}
