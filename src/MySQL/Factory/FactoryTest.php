<?php

declare(strict_types=1);

namespace QB\MySQL\Factory;

use QB\Generic\Factory\FactoryTest as GenericFactoryTest;
use QB\Generic\Factory\IFactory;
use QB\MySQL\Statement\Select;

class FactoryTest extends GenericFactoryTest
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

        $this->assertInstanceOf(Select::class, $select);
        $this->assertSame("SELECT *\nFROM foo", (string)$select);
    }
}
