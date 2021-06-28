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
            'param-unnamed-auto-null'  => [
                [null],
                Params::ALL_AUTO,
                [[null, PDO::PARAM_NULL]],
                true,
            ],
            'param-unnamed-auto-int'   => [
                [2],
                Params::ALL_AUTO,
                [[2, PDO::PARAM_INT]],
                true,
            ],
            'param-unnamed-auto-false' => [
                [false],
                Params::ALL_AUTO,
                [[false, PDO::PARAM_BOOL]],
                true,
            ],
            'param-unnamed-auto-true'  => [
                [true],
                Params::ALL_AUTO,
                [[true, PDO::PARAM_BOOL]],
                true,
            ],
            'param-unnamed-auto-str'   => [
                ['bar'],
                Params::ALL_AUTO,
                [['bar', PDO::PARAM_STR]],
                true,
            ],
            'param-unnamed-str'        => [
                [2],
                Params::ALL_STRING,
                [[2, PDO::PARAM_STR]],
                true,
            ],
            'param-unnamed-manual'     => [
                [[2, PDO::PARAM_BOOL]],
                Params::ALL_MANUAL,
                [[2, PDO::PARAM_BOOL]],
                true,
            ],
            'param-named-auto'         => [
                ['foo' => 2],
                Params::ALL_AUTO,
                ['foo' => [2, PDO::PARAM_INT]],
                false,
            ],
            'param-named-str'          => [
                ['foo' => 2],
                Params::ALL_STRING,
                ['foo' => [2, PDO::PARAM_STR]],
                false,
            ],
            'param-named-manual'       => [
                ['foo' => [2, PDO::PARAM_BOOL]],
                Params::ALL_MANUAL,
                ['foo' => [2, PDO::PARAM_BOOL]],
                false,
            ],
        ];
    }

    /**
     * @dataProvider successProvider
     *
     * @param array $params
     * @param int   $paramHandle
     * @param array $expectedParams
     * @param bool  $usesUnnamedParams
     */
    public function testSuccess(
        array $params,
        int $paramHandle,
        array $expectedParams,
        bool $usesUnnamedParams
    ) {
        $sut = $this->createSut($params, $paramHandle);

        $this->assertSame($expectedParams, $sut->getAll());

        if ($usesUnnamedParams) {
            $this->assertTrue($sut->usesUnnamedParams());
            $this->assertFalse($sut->usesNamedParams());
        } else {
            $this->assertFalse($sut->usesUnnamedParams());
            $this->assertTrue($sut->usesNamedParams());
        }
    }

    public function constructExceptionsProvider(): array
    {
        return [
            'invalid parameter handling'        => [[0 => null], 123],
            'string parameter key not expected' => [[0 => null, 'foo' => null], 123],
            'int parameter key not expected'    => [[1 => null], Params::ALL_AUTO],
            'int key parameter skipped'         => [[0 => null, 3 => null], Params::ALL_AUTO],
        ];
    }

    /**
     * @dataProvider constructExceptionsProvider
     *
     * @param array $params
     * @param int   $paramHandle
     */
    public function testConstructExceptions(array $params, int $paramHandle)
    {
        $this->expectException(InvalidArgumentException::class);

        $this->createSut($params, $paramHandle);
    }

    /**
     * @param array $params
     * @param int   $paramHandle
     *
     * @return Params
     */
    protected function createSut(array $params, int $paramHandle): Params
    {
        return new Params($params, $paramHandle);
    }
}
