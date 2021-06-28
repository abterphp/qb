<?php

declare(strict_types=1);

namespace QB\Generic\Params;

use InvalidArgumentException;
use PDO;
use PHPUnit\Framework\TestCase;

class ParamsTest extends TestCase
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
                [null],
                Params::ALL_AUTO,
                [[null, PDO::PARAM_NULL]],
            ],
            'param-unnamed-auto-int'        => [
                [2],
                Params::ALL_AUTO,
                [[2, PDO::PARAM_INT]],
            ],
            'param-unnamed-auto-false'      => [
                [false],
                Params::ALL_AUTO,
                [[false, PDO::PARAM_BOOL]],
            ],
            'param-unnamed-auto-true'       => [
                [true],
                Params::ALL_AUTO,
                [[true, PDO::PARAM_BOOL]],
            ],
            'param-unnamed-auto-str'        => [
                ['bar'],
                Params::ALL_AUTO,
                [['bar', PDO::PARAM_STR]],
            ],
            'param-unnamed-str'             => [
                [2],
                Params::ALL_STRING,
                [[2, PDO::PARAM_STR]],
            ],
            'param-unnamed-manual'          => [
                [[2, PDO::PARAM_BOOL]],
                Params::ALL_MANUAL,
                [[2, PDO::PARAM_BOOL]],
            ],
            'param-named-auto'              => [
                ['foo' => 2],
                Params::ALL_AUTO,
                ['foo' => [2, PDO::PARAM_INT]],
            ],
            'param-named-str'               => [
                ['foo' => 2],
                Params::ALL_STRING,
                ['foo' => [2, PDO::PARAM_STR]],
            ],
            'param-named-manual'            => [
                ['foo' => [2, PDO::PARAM_BOOL]],
                Params::ALL_MANUAL,
                ['foo' => [2, PDO::PARAM_BOOL]],
            ],
        ];
    }

    /**
     * @dataProvider successProvider
     *
     * @param array  $params
     * @param int    $paramHandle
     * @param array  $expectedParams
     */
    public function testSuccess(
        array $params,
        int $paramHandle,
        array $expectedParams
    ) {
        $sut = $this->createSut($params, $paramHandle);

        $this->assertSame($expectedParams, $sut->getAll());
    }

    public function constructExceptionsProvider(): array
    {
        return [
            'invalid parameter handling'        => [[0 => null], 123],
            'string parameter key not expected' => [[0 => null, 'foo' => null], 123],
            'int parameter key not expected'    => [[1 => null], Params::ALL_AUTO],
        ];
    }

    /**
     * @dataProvider constructExceptionsProvider
     *
     * @param array  $params
     * @param int    $paramHandle
     */
    public function testConstructExceptions(array $params, int $paramHandle)
    {
        $this->expectException(InvalidArgumentException::class);

        $this->createSut($params, $paramHandle);
    }

    /**
     * @param array  $params
     * @param int    $paramHandle
     *
     * @return Params
     */
    protected function createSut(array $params, int $paramHandle): Params
    {
        return new Params($params, $paramHandle);
    }
}
