<?php

declare(strict_types=1);

namespace QB\Generic\Template;

use PHPUnit\Framework\TestCase;
use QB\Generic\Expr\Expr;

class TemplateTest extends TestCase
{
    public function testToString()
    {
        $expectedResult = 'COUNT(foo) > 0 AND 3 = (2 + ?)';

        $sut = new Template('COUNT(%s) > 0 AND %s = %s', new Expr('foo'), new Expr('3'), new Expr('(2 + ?)'));

        $actualResult = (string)$sut;

        $this->assertSame($expectedResult, $actualResult);
    }

    public function testGetParams()
    {
        $expectedResult = [':val' => [3, \PDO::PARAM_INT], [1, \PDO::PARAM_INT]];

        $a = new Expr('foo');
        $b = new Expr(':val', [':val' => [3, \PDO::PARAM_INT]]);
        $c = new Expr('(2 + ?)', [[1, \PDO::PARAM_INT]]);

        $sut = new Template('COUNT(%s) > 0 AND :val = %s', $a, $b, $c);

        $actualResult = $sut->getParams();

        $this->assertSame($expectedResult, $actualResult);
    }
}
