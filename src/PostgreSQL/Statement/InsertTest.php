<?php

declare(strict_types=1);

namespace QB\PostgreSQL\Statement;

use QB\Generic\Clause\Table;
use QB\Generic\Expr\Expr;
use QB\Generic\Statement\InsertTest as GenericInsertTest;
use RuntimeException;

class InsertTest extends GenericInsertTest
{
    /**
     * @suppress PhanNoopCast
     */
    public function testToStringThrowsAnExceptionIfNotInitialized()
    {
        $this->expectException(RuntimeException::class);

        (string)(new Insert());
    }

    public function testWitDefaultValues()
    {
        $sql = (string)$this->getSut('foo');

        $parts   = [];
        $parts[] = 'INSERT INTO foo';
        $parts[] = 'DEFAULT VALUES';

        $expectedSql = implode(PHP_EOL, $parts);

        $this->assertSame($expectedSql, $sql);
    }

    public function testComplex()
    {
        $sql = (string)$this->getSut('foo')
            ->setColumns('id', 'bar_id', 'baz')
            ->addValues('1234', new Expr('?', ['a']), '"a"')
            ->addValues('3456', '4567', '"b"');

        $parts   = [];
        $parts[] = 'INSERT INTO foo (id, bar_id, baz)';
        $parts[] = 'VALUES (1234, ?, "a"),';
        $parts[] = '(3456, 4567, "b")';

        $expectedSql = implode(PHP_EOL, $parts);

        $this->assertSame($expectedSql, $sql);
    }

    public function testAddMultipleRows()
    {
        $query = $this->getSut('offices')
            ->setInto(new Table('offices'))
            ->setColumns('officeCode', 'city', 'phone', 'addressLine1', 'country', 'postalCode', 'territory')
            ->addValues('"abc"', '"Berlin"', '"+49 101 123 4567"', '""', '"Germany"', '"10111"', '"NA"')
            ->addValues('"bcd"', '"Budapest"', '"+36 70 101 1234"', '""', '"Hungary"', '"1011"', '"NA"')
            ->addValues('"cde"', '"Pécs"', '"+36 70 222 3456"', '"Rákóczi út"', '"Hungary"', '"723"', '"NA"')
            ->setReturning('*');

        $parts   = [];
        $parts[] = 'INSERT INTO offices (officeCode, city, phone, addressLine1, country, postalCode, territory)';
        $parts[] = 'VALUES ("abc", "Berlin", "+49 101 123 4567", "", "Germany", "10111", "NA"),';
        $parts[] = '("bcd", "Budapest", "+36 70 101 1234", "", "Hungary", "1011", "NA"),';
        $parts[] = '("cde", "Pécs", "+36 70 222 3456", "Rákóczi út", "Hungary", "723", "NA")';
        $parts[] = 'RETURNING *';

        $expectedSql = implode(PHP_EOL, $parts);

        $this->assertSame($expectedSql, (string)$query);
    }

    public function testAddOnConflictDoNothing()
    {
        $query = $this->getSut('offices')
            ->setInto(new Table('offices'))
            ->setColumns('officeCode', 'city', 'phone', 'addressLine1', 'country', 'postalCode', 'territory')
            ->addValues('"abc"', '"Berlin"', '"+49 101 123 4567"', '""', '"Germany"', '"10111"', '"NA"')
            ->setDoNothing();

        $parts   = [];
        $parts[] = 'INSERT INTO offices (officeCode, city, phone, addressLine1, country, postalCode, territory)';
        $parts[] = 'VALUES ("abc", "Berlin", "+49 101 123 4567", "", "Germany", "10111", "NA")';
        $parts[] = 'ON CONFLICT DO NOTHING';

        $expectedSql = implode(PHP_EOL, $parts);

        $this->assertSame($expectedSql, (string)$query);
    }

    public function testAddOnConflictDoUpdate()
    {
        $query = $this->getSut('offices')
            ->setInto(new Table('offices'))
            ->setColumns('officeCode', 'city', 'phone', 'addressLine1', 'country', 'postalCode', 'territory')
            ->addValues('"abc"', '"Berlin"', '"+49 101 123 4567"', '""', '"Germany"', '"10111"', '"NA"')
            ->setOnConflict('officeCode', 'city')
            ->setDoUpdate('officeCode = EXCLUDED.officeCode', 'city = EXCLUDED.city')
            ->setReturning('*');

        $parts   = [];
        $parts[] = 'INSERT INTO offices (officeCode, city, phone, addressLine1, country, postalCode, territory)';
        $parts[] = 'VALUES ("abc", "Berlin", "+49 101 123 4567", "", "Germany", "10111", "NA")';
        $parts[] = 'ON CONFLICT (officeCode, city) DO UPDATE';
        $parts[] = 'SET officeCode = EXCLUDED.officeCode, city = EXCLUDED.city';
        $parts[] = 'RETURNING *';

        $expectedSql = implode(PHP_EOL, $parts);

        $this->assertSame($expectedSql, (string)$query);
    }

    /**
     * @param string $table
     *
     * @return Insert
     */
    protected function getSut(string $table): Insert
    {
        return (new Insert())->setInto($table);
    }
}
