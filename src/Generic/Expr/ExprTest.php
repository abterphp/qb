<?php

declare(strict_types=1);

namespace QB\Generic\Expr;

use InvalidArgumentException;
use PDO;
use PHPUnit\Framework\TestCase;

class ExprTest extends TestCase
{
    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     *
     * @return array[]
     */
    public function successProvider(): array
    {
        return [
            'param-unnamed-auto-null'       => [
                'foo = ?',
                [null],
                Expr::PARAM_ALL_AUTO,
                'foo = ?',
                [[null, PDO::PARAM_NULL]],
            ],
            'param-unnamed-auto-int'        => ['foo = ?', [2], Expr::PARAM_ALL_AUTO, 'foo = ?', [[2, PDO::PARAM_INT]]],
            'param-unnamed-auto-false'      => [
                'foo = ?',
                [false],
                Expr::PARAM_ALL_AUTO,
                'foo = ?',
                [[false, PDO::PARAM_BOOL]],
            ],
            'param-unnamed-auto-true'       => [
                'foo = ?',
                [true],
                Expr::PARAM_ALL_AUTO,
                'foo = ?',
                [[true, PDO::PARAM_BOOL]],
            ],
            'param-unnamed-auto-str'        => [
                'foo = ?',
                ['bar'],
                Expr::PARAM_ALL_AUTO,
                'foo = ?',
                [['bar', PDO::PARAM_STR]],
            ],
            'param-unnamed-str'             => [
                'foo = ?',
                [2],
                Expr::PARAM_ALL_STRING,
                'foo = ?',
                [[2, PDO::PARAM_STR]],
            ],
            'param-unnamed-manual'          => [
                'foo = ?',
                [[2, PDO::PARAM_BOOL]],
                Expr::PARAM_ALL_MANUAL,
                'foo = ?',
                [[2, PDO::PARAM_BOOL]],
            ],
            'param-named-auto'              => [
                'foo = :foo',
                ['foo' => 2],
                Expr::PARAM_ALL_AUTO,
                'foo = :foo',
                ['foo' => [2, PDO::PARAM_INT]],
            ],
            'param-named-str'               => [
                'foo = :foo',
                ['foo' => 2],
                Expr::PARAM_ALL_STRING,
                'foo = :foo',
                ['foo' => [2, PDO::PARAM_STR]],
            ],
            'param-named-manual'            => [
                'foo = :foo',
                ['foo' => [2, PDO::PARAM_BOOL]],
                Expr::PARAM_ALL_MANUAL,
                'foo = :foo',
                ['foo' => [2, PDO::PARAM_BOOL]],
            ],
            'unnamed-extend-auto-simple'    => [
                'foo IN (?)',
                [['bar', 'baz']],
                Expr::PARAM_ALL_AUTO,
                'foo IN (?, ?)',
                [['bar', PDO::PARAM_STR], ['baz', PDO::PARAM_STR]],
            ],
            'unnamed-extend-auto-end'       => [
                'foo IN (?',
                [['bar', 'baz']],
                Expr::PARAM_ALL_AUTO,
                'foo IN (?, ?',
                [['bar', PDO::PARAM_STR], ['baz', PDO::PARAM_STR]],
            ],
            'unnamed-extend-auto-beginning' => [
                '?)',
                [['bar', 'baz']],
                Expr::PARAM_ALL_AUTO,
                '?, ?)',
                [['bar', PDO::PARAM_STR], ['baz', PDO::PARAM_STR]],
            ],
            'unnamed-extend-auto-complex'   => [
                'foo IN (?) AND bar = ? AND baz IN (?)',
                [['bar', 'baz'], 'quix', [17, 34]],
                Expr::PARAM_ALL_AUTO,
                'foo IN (?, ?) AND bar = ? AND baz IN (?, ?)',
                [
                    ['bar', PDO::PARAM_STR],
                    ['baz', PDO::PARAM_STR],
                    ['quix', PDO::PARAM_STR],
                    [17, PDO::PARAM_INT],
                    [34, PDO::PARAM_INT],
                ],
            ],
            'unnamed-extend-manual-complex' => [
                'foo IN (?) AND bar = ? AND baz IN (?)',
                [
                    [['bar', PDO::PARAM_STR], ['baz', PDO::PARAM_STR]],
                    ['quix', PDO::PARAM_STR],
                    [[17, PDO::PARAM_INT], [34, PDO::PARAM_STR]],
                ],
                Expr::PARAM_ALL_MANUAL,
                'foo IN (?, ?) AND bar = ? AND baz IN (?, ?)',
                [
                    ['bar', PDO::PARAM_STR],
                    ['baz', PDO::PARAM_STR],
                    ['quix', PDO::PARAM_STR],
                    [17, PDO::PARAM_INT],
                    [34, PDO::PARAM_STR],
                ],
            ],
            'unnamed-extend-str-complex'    => [
                'foo IN (?) AND bar = ? AND baz IN (?)',
                [['bar', 'baz'], 'quix', [17, 34]],
                Expr::PARAM_ALL_STRING,
                'foo IN (?, ?) AND bar = ? AND baz IN (?, ?)',
                [
                    ['bar', PDO::PARAM_STR],
                    ['baz', PDO::PARAM_STR],
                    ['quix', PDO::PARAM_STR],
                    [17, PDO::PARAM_STR],
                    [34, PDO::PARAM_STR],
                ],
            ],
            'named-extend-auto-simple'      => [
                'foo IN (:foo)',
                ['foo' => ['bar', 'baz']],
                Expr::PARAM_ALL_AUTO,
                'foo IN (:foo_0, :foo_1)',
                ['foo_0' => ['bar', PDO::PARAM_STR], 'foo_1' => ['baz', PDO::PARAM_STR]],
            ],
            'named-extend-auto-end'         => [
                'foo IN (:foo',
                ['foo' => ['bar', 'baz']],
                Expr::PARAM_ALL_AUTO,
                'foo IN (:foo_0, :foo_1',
                ['foo_0' => ['bar', PDO::PARAM_STR], 'foo_1' => ['baz', PDO::PARAM_STR]],
            ],
            'named-extend-auto-beginning'   => [
                ':foo)',
                ['foo' => ['bar', 'baz']],
                Expr::PARAM_ALL_AUTO,
                ':foo_0, :foo_1)',
                ['foo_0' => ['bar', PDO::PARAM_STR], 'foo_1' => ['baz', PDO::PARAM_STR]],
            ],
            'named-extend-auto-complex'     => [
                'foo IN (:foo) AND bar = :bar AND baz IN (:baz)',
                ['foo' => ['bar', 'baz'], 'bar' => 'quix', 'baz' => [17, 34]],
                Expr::PARAM_ALL_AUTO,
                'foo IN (:foo_0, :foo_1) AND bar = :bar AND baz IN (:baz_0, :baz_1)',
                [
                    'foo_0' => ['bar', PDO::PARAM_STR],
                    'foo_1' => ['baz', PDO::PARAM_STR],
                    'bar'   => ['quix', PDO::PARAM_STR],
                    'baz_0' => [17, PDO::PARAM_INT],
                    'baz_1' => [34, PDO::PARAM_INT],
                ],
            ],
            'named-extend-manual-complex'   => [
                'foo IN (:foo) AND bar = :bar AND baz IN (:baz)',
                [
                    'foo' => [['bar', PDO::PARAM_STR], ['baz', PDO::PARAM_STR]],
                    'bar' => ['quix', PDO::PARAM_STR],
                    'baz' => [[17, PDO::PARAM_INT], [34, PDO::PARAM_STR]],
                ],
                Expr::PARAM_ALL_MANUAL,
                'foo IN (:foo_0, :foo_1) AND bar = :bar AND baz IN (:baz_0, :baz_1)',
                [
                    'foo_0' => ['bar', PDO::PARAM_STR],
                    'foo_1' => ['baz', PDO::PARAM_STR],
                    'bar'   => ['quix', PDO::PARAM_STR],
                    'baz_0' => [17, PDO::PARAM_INT],
                    'baz_1' => [34, PDO::PARAM_STR],
                ],
            ],
            'named-extend-str-complex'      => [
                'foo IN (:foo) AND bar = :bar AND baz IN (:baz)',
                ['foo' => ['bar', 'baz'], 'bar' => 'quix', 'baz' => [17, 34]],
                Expr::PARAM_ALL_STRING,
                'foo IN (:foo_0, :foo_1) AND bar = :bar AND baz IN (:baz_0, :baz_1)',
                [
                    'foo_0' => ['bar', PDO::PARAM_STR],
                    'foo_1' => ['baz', PDO::PARAM_STR],
                    'bar'   => ['quix', PDO::PARAM_STR],
                    'baz_0' => [17, PDO::PARAM_STR],
                    'baz_1' => [34, PDO::PARAM_STR],
                ],
            ],
        ];
    }

    /**
     * @dataProvider successProvider
     *
     * @param string $sql
     * @param array  $params
     * @param int    $paramHandle
     * @param string $expectedSql
     * @param array  $expectedParams
     */
    public function testSuccess(
        string $sql,
        array $params,
        int $paramHandle,
        string $expectedSql,
        array $expectedParams
    ) {
        $sut = $this->createSut($sql, $params, $paramHandle);

        $this->assertSame($expectedSql, (string)$sut);
        $this->assertSame($expectedParams, $sut->getParams());
    }

    public function constructExceptionsProvider(): array
    {
        return [
            'object parameter'                       => ['foo = ?', [new \stdClass()], Expr::PARAM_ALL_AUTO],
            'invalid parameter handling'             => ['foo', [0 => null], 123],
            'string parameter key not expected'      => ['foo', [0 => null, 'foo' => null], 123],
            'int parameter key not expected'         => ['foo', [1 => null], Expr::PARAM_ALL_AUTO],
            'missing int parameter key not expected' => ['foo', [0 => null, 2 => null], Expr::PARAM_ALL_AUTO],
        ];
    }

    /**
     * @dataProvider constructExceptionsProvider
     *
     * @param string $sql
     * @param array  $params
     * @param int    $paramHandle
     */
    public function testConstructExceptions(string $sql, array $params, int $paramHandle)
    {
        $this->expectException(InvalidArgumentException::class);

        $this->createSut($sql, $params, $paramHandle);
    }

    /**
     * @param string $sql
     * @param array  $params
     * @param int    $paramHandle
     *
     * @return Expr
     */
    protected function createSut(string $sql, array $params, int $paramHandle): Expr
    {
        return new Expr($sql, $params, $paramHandle);
    }
}
