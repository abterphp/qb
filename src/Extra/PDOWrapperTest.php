<?php

declare(strict_types=1);

namespace QB\Extra;

use PDO;
use PDOStatement;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use QB\Generic\Expr\Expr;

class PDOWrapperTest extends TestCase
{
    /** @var PDOWrapper System Under Test */
    protected PDOWrapper $sut;

    /** @var PDO|MockObject */
    protected PDO|MockObject $pdoMock;

    /**
     * @suppress PhanTypeMismatchArgument
     */
    public function setUp(): void
    {
        /** @var PDO $pdoMock */
        $pdoMock = $this->getMockBuilder(PDO::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->pdoMock = $pdoMock;
        $this->sut = new PDOWrapper($pdoMock);
    }

    /**
     * @suppress PhanTypeMismatchArgumentProbablyReal
     */
    public function testPrepareNumeric()
    {
        $sql    = '? AND ?';
        $values = [17, 23];

        $statementMock = $this->getMockBuilder(PDOStatement::class)->getMock();
        $statementMock
            ->expects($this->exactly(count($values)))
            ->method('bindParam')
            ->withConsecutive([1, 17, PDO::PARAM_INT], [2, 23, PDO::PARAM_INT]);

        $this->pdoMock->expects($this->once())->method('prepare')->with($sql)->willReturn($statementMock);

        $query = new Expr($sql, $values);

        $this->sut->prepare($query);
    }

    /**
     * @suppress PhanTypeMismatchArgumentProbablyReal
     */
    public function testPrepareAssoc()
    {
        $sql    = ':foo AND :bar';
        $values = ['foo' => 'bar', 'bar' => 'baz'];

        $statementMock = $this->getMockBuilder(PDOStatement::class)->getMock();
        $statementMock
            ->expects($this->exactly(count($values)))
            ->method('bindParam')
            ->withConsecutive(['foo', 'bar', PDO::PARAM_STR], ['bar', 'baz', PDO::PARAM_STR]);

        $this->pdoMock->expects($this->once())->method('prepare')->with($sql)->willReturn($statementMock);

        $query = new Expr($sql, $values);

        $this->sut->prepare($query);
    }

    /**
     * @suppress PhanTypeMismatchArgumentProbablyReal
     */
    public function testExecute()
    {
        $returnValue = true;

        $sql    = ':foo AND :bar';
        $values = ['foo' => 'bar', 'bar' => 'baz'];

        $statementMock = $this->getMockBuilder(PDOStatement::class)->getMock();
        $statementMock
            ->expects($this->exactly(count($values)))
            ->method('bindParam')
            ->withConsecutive(['foo', 'bar', PDO::PARAM_STR], ['bar', 'baz', PDO::PARAM_STR]);
        $statementMock
            ->expects($this->once())
            ->method('execute')
            ->willReturn($returnValue);

        $this->pdoMock->expects($this->once())->method('prepare')->with($sql)->willReturn($statementMock);

        $query = new Expr($sql, $values);

        $actualValue = $this->sut->execute($query);

        $this->assertSame($returnValue, $actualValue);
    }

    /**
     * @suppress PhanTypeMismatchArgumentProbablyReal
     */
    public function testFetchAll()
    {
        $returnValue = [['foo', 'bar'], ['bar', 'baz']];

        $sql    = ':foo AND :bar';
        $values = ['foo' => 'bar', 'bar' => 'baz'];

        $statementMock = $this->getMockBuilder(PDOStatement::class)->getMock();
        $statementMock
            ->expects($this->exactly(count($values)))
            ->method('bindParam')
            ->withConsecutive(['foo', 'bar', PDO::PARAM_STR], ['bar', 'baz', PDO::PARAM_STR]);
        $statementMock
            ->expects($this->once())
            ->method('execute')
            ->willReturn(true);
        $statementMock
            ->expects($this->once())
            ->method('fetchAll')
            ->willReturn($returnValue);

        $this->pdoMock->expects($this->once())->method('prepare')->with($sql)->willReturn($statementMock);

        $query = new Expr($sql, $values);

        $actualValue = $this->sut->fetchAll($query);

        $this->assertSame($returnValue, $actualValue);
    }

    /**
     * @suppress PhanTypeMismatchArgumentProbablyReal
     */
    public function testFetchColumn()
    {
        $returnValue = 123;

        $sql    = ':foo AND :bar';
        $values = ['foo' => 'bar', 'bar' => 'baz'];

        $statementMock = $this->getMockBuilder(PDOStatement::class)->getMock();
        $statementMock
            ->expects($this->exactly(count($values)))
            ->method('bindParam')
            ->withConsecutive(['foo', 'bar', PDO::PARAM_STR], ['bar', 'baz', PDO::PARAM_STR]);
        $statementMock
            ->expects($this->once())
            ->method('execute')
            ->willReturn(true);
        $statementMock
            ->expects($this->once())
            ->method('fetchColumn')
            ->willReturn($returnValue);

        $this->pdoMock->expects($this->once())->method('prepare')->with($sql)->willReturn($statementMock);

        $query = new Expr($sql, $values);

        $actualValue = $this->sut->fetchColumn($query);

        $this->assertSame($returnValue, $actualValue);
    }

    /**
     * @suppress PhanTypeMismatchArgumentProbablyReal
     */
    public function testFetch()
    {
        $returnValue = ['foo', 'bar'];

        $sql    = ':foo AND :bar';
        $values = ['foo' => 'bar', 'bar' => 'baz'];

        $statementMock = $this->getMockBuilder(PDOStatement::class)->getMock();
        $statementMock
            ->expects($this->exactly(count($values)))
            ->method('bindParam')
            ->withConsecutive(['foo', 'bar', PDO::PARAM_STR], ['bar', 'baz', PDO::PARAM_STR]);
        $statementMock
            ->expects($this->once())
            ->method('execute')
            ->willReturn(true);
        $statementMock
            ->expects($this->once())
            ->method('fetch')
            ->willReturn($returnValue);

        $this->pdoMock->expects($this->once())->method('prepare')->with($sql)->willReturn($statementMock);

        $query = new Expr($sql, $values);

        $actualValue = $this->sut->fetch($query);

        $this->assertSame($returnValue, $actualValue);
    }
}
