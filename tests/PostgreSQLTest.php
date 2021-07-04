<?php

declare(strict_types=1);

namespace QB\Tests;

use PDO;
use PHPUnit\Framework\TestCase;
use QB\Extra\PDOWrapper;
use QB\Generic\Clause\Column;
use QB\Generic\Clause\Table;
use QB\Generic\Expr\Expr;
use QB\PostgreSQL\QueryBuilder\QueryBuilder;

class PostgreSQLTest extends TestCase
{
    /** @var QueryBuilder */
    protected QueryBuilder $sut;

    protected PDO $pdo;

    protected PDOWrapper $pdoWrapper;

    public function setUp(): void
    {
        if (!getenv('POSTGRES_USER')) {
            $this->markTestSkipped('no db');
        }

        $this->sut = new QueryBuilder();

        $dns      = sprintf(
            'pgsql:dbname=%s;host=%s',
            getenv('POSTGRES_DB'),
            'postgres'
        );
        $username = getenv('POSTGRES_USER');
        $password = getenv('POSTGRES_PASSWORD');
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
            ->innerJoin('offices', 'employees.officeCode = o.officeCode', 'o')
            ->where(new Expr('employees.jobTitle = ?', ['Sales Rep']))
            ->where('o.city = \'NYC\'')
            ->addUnion($unionQuery)
            ->setOuterOrderBy('type', 'DESC')
            ->setOuterOrderBy('lastName')
            ->setOuterLimit($limit);

        $this->assertCount($limit, $this->pdoWrapper->fetchAll($query, PDO::FETCH_ASSOC));
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
                ->where("officeCode = 'oc'");

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

    public function testUpsert()
    {
        try {
            $this->pdo->exec('BEGIN');

            // INSERT
            $query = $this->sut->insert()
                ->into(new Table('offices'))
                ->columns('officeCode', 'city', 'phone', 'addressLine1', 'country', 'postalCode', 'territory')
                ->values("'abc'", "'Berlin'", "'+49 101 123 4567'", "''", "'Germany'", "'10111'", "'NA'")
                ->values("'bcd'", "'Budapest'", "'+36 70 101 1234'", "''", "'Hungary'", "'1011'", "'NA'")
                ->setReturning('*');

            $values = $this->pdoWrapper->fetchAll($query, \PDO::FETCH_ASSOC);

            $expectedValues = [
                [
                    'officecode'   => 'abc',
                    'city'         => 'Berlin',
                    'phone'        => '+49 101 123 4567',
                    'addressline1' => '',
                    'addressline2' => null,
                    'state'        => null,
                    'country'      => 'Germany',
                    'postalcode'   => '10111',
                    'territory'    => 'NA',
                ],
                [
                    'officecode'   => 'bcd',
                    'city'         => 'Budapest',
                    'phone'        => '+36 70 101 1234',
                    'addressline1' => '',
                    'addressline2' => null,
                    'state'        => null,
                    'country'      => 'Hungary',
                    'postalcode'   => '1011',
                    'territory'    => 'NA',
                ],
            ];
            $this->assertSame($expectedValues, $values);

            // DELETE
            $query = $this->sut->delete()
                ->from(new Table('offices'))
                ->where(new Expr('officeCode IN (?)', [['abc', 'bcd']]));

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
