<?php

declare(strict_types=1);

namespace QB\PostgreSQL\Factory;

use QB\Generic\Factory\IFactory;
use QB\Generic\Factory\FactoryTest as GenericFactoryTest;

class FactoryTest extends GenericFactoryTest
{
    /** @var Factory */
    protected IFactory $sut;

    public function setUp(): void
    {
        $this->sut = new Factory();
    }
}
