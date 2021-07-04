<?php

declare(strict_types=1);

namespace QB\Generic\QueryBuilder;

use PHPUnit\Framework\TestCase;
use QB\Generic\Statement\Delete;
use QB\Generic\Statement\Insert;
use QB\Generic\Statement\Select;
use QB\Generic\Statement\Truncate;
use QB\Generic\Statement\Update;

class QueryBuilderTest extends TestCase
{
    /** @var QueryBuilder */
    protected IQueryBuilder $sut;

    public function setUp(): void
    {
        $this->sut = new QueryBuilder();
    }

    public function testSelect()
    {
        $select = $this->sut->select()->from('foo');

        $sql = (string)$select;

        $this->assertInstanceOf(Select::class, $select);
        $this->assertSame("SELECT *\nFROM foo", $sql);
    }

    public function testInsert()
    {
        $insert = $this->sut->insert()->into('foo')->columns('bar')->addValues("'Bar'");

        $sql = (string)$insert;

        $this->assertInstanceOf(Insert::class, $insert);
        $this->assertSame("INSERT INTO foo (bar)\nVALUES ('Bar')", $sql);
    }

    public function testUpdate()
    {
        $update = $this->sut->update('foo')->setValues(['bar' => "'Bar'"])->where('1');

        $sql = (string)$update;

        $this->assertInstanceOf(Update::class, $update);
        $this->assertSame("UPDATE foo\nSET bar = 'Bar'\nWHERE 1", $sql);
    }

    public function testDelete()
    {
        $delete = $this->sut->delete()->from('foo')->where('1');

        $sql = (string)$delete;

        $this->assertInstanceOf(Delete::class, $delete);
        $this->assertSame("DELETE FROM foo\nWHERE 1", $sql);
    }

    public function testTruncate()
    {
        $truncate = $this->sut->truncate()->from('foo');

        $sql = (string)$truncate;

        $this->assertInstanceOf(Truncate::class, $truncate);
        $this->assertSame("TRUNCATE foo", $sql);
    }
}
