<?php

declare(strict_types=1);

namespace QB\Generic\Factory;

use PHPUnit\Framework\TestCase;
use QB\Generic\Statement\Delete;
use QB\Generic\Statement\Insert;
use QB\Generic\Statement\Select;
use QB\Generic\Statement\Truncate;
use QB\Generic\Statement\Update;

class FactoryTest extends TestCase
{
    /** @var Factory */
    protected IFactory $sut;

    public function setUp(): void
    {
        $this->sut = new Factory();
    }

    public function testSelect()
    {
        $select = $this->sut->select()->addFrom('foo');

        $sql = (string)$select;

        $this->assertInstanceOf(Select::class, $select);
        $this->assertSame("SELECT *\nFROM foo", $sql);
    }

    public function testInsert()
    {
        $insert = $this->sut->insert()->setInto('foo')->addValues(['bar' => 'Bar']);

        $sql = (string)$insert;

        $this->assertInstanceOf(Insert::class, $insert);
        $this->assertSame("INSERT INTO foo\nVALUES (?)", $sql);
    }

    public function testUpdate()
    {
        $update = $this->sut->update()->addFrom('foo')->setValues(['bar' => 'Bar'])->addWhere('1');

        $sql = (string)$update;

        $this->assertInstanceOf(Update::class, $update);
        $this->assertSame("UPDATE foo\nSET bar = ?\nWHERE 1", $sql);
    }

    public function testDelete()
    {
        $delete = $this->sut->delete()->addFrom('foo')->addWhere('1');

        $sql = (string)$delete;

        $this->assertInstanceOf(Delete::class, $delete);
        $this->assertSame("DELETE FROM foo\nWHERE 1", $sql);
    }

    public function testTruncate()
    {
        $truncate = $this->sut->truncate()->addFrom('foo');

        $sql = (string)$truncate;

        $this->assertInstanceOf(Truncate::class, $truncate);
        $this->assertSame("TRUNCATE foo", $sql);
    }
}
