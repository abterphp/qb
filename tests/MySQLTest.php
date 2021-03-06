<?php

declare(strict_types=1);

namespace QB\Tests;

use PDO;
use PHPUnit\Framework\TestCase;
use QB\Extra\PDOWrapper;
use QB\Generic\Clause\Column;
use QB\Generic\Clause\Table;
use QB\Generic\Expr\Expr;
use QB\MySQL\QueryBuilder\QueryBuilder;
use QB\MySQL\Statement\Select;

class MySQLTest extends TestCase
{
    /** @var QueryBuilder */
    protected QueryBuilder $sut;

    protected PDO $pdo;

    protected PDOWrapper $pdoWrapper;

    public function setUp(): void
    {
        if (!getenv('MYSQL_USER')) {
            $this->markTestSkipped('no db');
        }

        $this->sut = new QueryBuilder();

        $dns      = sprintf(
            'mysql:dbname=%s;host=%s',
            getenv('MYSQL_DATABASE'),
            'mysql'
        );
        $username = getenv('MYSQL_USER');
        $password =getenv('MYSQL_PASSWORD');
        $options  = null;

        $this->pdo = new PDO($dns, $username, $password, $options);

        $this->pdoWrapper = new PDOWrapper($this->pdo);
    }

    public function testSelectOneCustomer()
    {
        $sql = (string)$this->sut->select()
            ->from('customers')
            ->columns('customerName')
            ->limit(1);

        $statement = $this->pdo->query($sql);

        $this->assertSame('Atelier graphique', $statement->fetchColumn());
    }

    public function testSelectComplex()
    {
        $limit = 5;

        $columnQuery = $this->sut->select()
            ->from(new Table('employees', 'boss'))
            ->columns('lastName')
            ->where(new Expr('boss.employeeNumber = employees.reportsTo'));

        $customerTypeColumn = new Column(new Expr("'customers'"), 'type');
        $employeeTypeColumn = new Column(new Expr("'employees'"), 'type');

        $unionQuery = $this->sut->select()
            ->from('customers')
            ->columns('contactLastName', 'NULL', $customerTypeColumn);

        $query = $this->sut->select()
            ->from('employees')
            ->columns('lastName', new Column($columnQuery, 'bossLastName'), $employeeTypeColumn)
            ->innerJoin(new Table('offices', 'o'), 'employees.officeCode = o.officeCode')
            ->where(new Expr('employees.jobTitle = ?', ['Sales Rep']))
            ->where('o.city = \'NYC\'')
            ->union($unionQuery)
            ->outerOrderBy('type', Select::DIRECTION_DESC)
            ->outerOrderBy('lastName')
            ->outerLimit($limit);

        $this->assertCount($limit, $this->pdoWrapper->fetchAll($query, PDO::FETCH_ASSOC));
    }

    /**
     * @deprecated feature, reasons below
     *
     * The SQL_CALC_FOUND_ROWS query modifier and accompanying FOUND_ROWS() function are deprecated as of MySQL 8.0.17
     * and will be removed in a future MySQL version.
     *
     * COUNT(*) is subject to certain optimizations. SQL_CALC_FOUND_ROWS causes some optimizations to be disabled.
     *
     * Use these queries instead:
     */
    public function testSelectWithCalcFoundRows()
    {
        $limit = 5;

        $sql = (string)$this->sut->select()
            ->from('customers')
            ->modifier(Select::SQL_CALC_FOUND_ROWS)
            ->columns('customerName')
            ->limit($limit);

        $statement = $this->pdo->query($sql);

        $this->assertCount($limit, $statement->fetchAll());

        $sql = (string)$this->sut->select()
            ->columns('FOUND_ROWS()');

        $statement = $this->pdo->query($sql);

        $this->assertGreaterThan($limit, (int)$statement->fetchColumn());
    }

    public function testInsertUpdateDelete()
    {
        try {
            $this->pdo->exec('BEGIN');

            // INSERT
            $query = $this->sut->insert()
                ->into(new Table('offices'))
                ->columns('officeCode', 'city', 'phone', 'addressLine1', 'country', 'postalCode', 'territory')
                ->values("'abc'", "'Berlin'", "'+49 101 123 4567'", "''", "'Germany'", "'10111'", "'NA'");

            $statement = $this->pdo->prepare((string)$query);

            $result = $statement->execute();
            $this->assertTrue($result);

            // UPDATE
            $query = $this->sut->update(new Table('offices'))
                ->values(['territory' => "'Berlin'"])
                ->where("officeCode = 'abc'");

            $this->assertTrue($this->pdoWrapper->execute($query));

            // DELETE
            $query = $this->sut->delete()
                ->from(new Table('offices'))
                ->where(new Expr('officeCode = ?', ['abc']));

            $this->assertTrue($this->pdoWrapper->execute($query));

            // COMMIT
            $this->pdo->exec('COMMIT');
        } catch (\Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->exec('ROLLBACK');
            }
            $this->fail($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }
    }
}
