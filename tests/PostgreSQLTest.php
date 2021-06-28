<?php

declare(strict_types=1);

namespace QB\Tests;

use PDO;
use PHPUnit\Framework\TestCase;
use QB\Generic\Clause\Column;
use QB\Generic\Clause\Table;
use QB\Generic\Expr\Expr;
use QB\PDOHelper;
use QB\PostgreSQL\Factory\Factory;

class PostgreSQLTest extends TestCase
{
    /** @var Factory */
    protected Factory $sut;

    protected \PDO $pdo;

    public function setUp(): void
    {
        if (!array_key_exists('POSTGRES_USER', $_ENV)) {
            $this->markTestSkipped('no db');
        }

        $this->sut = new Factory();

        $dns      = sprintf(
            'pgsql:dbname=%s;host=%s',
            $_ENV['POSTGRES_DB'],
            'postgres'
        );
        $username = $_ENV['POSTGRES_USER'];
        $password = $_ENV['POSTGRES_PASSWORD'];
        $options  = null;

        $this->pdo = new \PDO($dns, $username, $password, $options);
    }

    public function testSelectOneCustomer()
    {
        $sql = (string)$this->sut->select()
            ->addFrom('customers')
            ->addColumns('customerName')
            ->setLimit(1);

        $statement = $this->pdo->query($sql);

        $this->assertSame('Atelier graphique', $statement->fetchColumn());
    }

    public function testSelectComplex()
    {
        $limit = 5;

        $columnQuery = $this->sut->select()
            ->addFrom(new Table('employees', 'boss'))
            ->addColumns('lastName')
            ->addWhere(new Expr('boss.employeeNumber = employees.reportsTo'));

        $customerTypeColumn = new Column(new Expr("'customers'"), 'type');
        $employeeTypeColumn = new Column(new Expr("'employees'"), 'type');

        $unionQuery = $this->sut->select()
            ->addFrom('customers')
            ->addColumns('contactLastName', 'NULL', $customerTypeColumn);

        $query = $this->sut->select()
            ->addFrom('employees')
            ->addColumns('lastName', new Column($columnQuery, 'bossLastName'), $employeeTypeColumn)
            ->addInnerJoin('offices', 'employees.officeCode = o.officeCode', 'o')
            ->addWhere(new Expr('employees.jobTitle = ?', ['Sales Rep']))
            ->addWhere('o.city = \'NYC\'')
            ->addUnion($unionQuery)
            ->setOuterOrderBy('type', 'DESC')
            ->setOuterOrderBy('lastName')
            ->setOuterLimit($limit);

        $this->assertCount($limit, PDOHelper::fetchAll($this->pdo, $query, PDO::FETCH_ASSOC));
    }

    public function testInsertUpdateDelete()
    {
        try {
            $this->pdo->exec('BEGIN');

            // INSERT
            $query = $this->sut->insert()
                ->setInto(new Table('offices'))
                ->setColumns('officeCode', 'city', 'phone', 'addressLine1', 'country', 'postalCode', 'territory')
                ->addValues("'abc'", "'Berlin'", "'+49 101 123 4567'", "''", "'Germany'", "'10111'", "'NA'");

            $statement = $this->pdo->prepare((string)$query);

            $result = $statement->execute();
            $this->assertTrue($result);

            // UPDATE
            $query = $this->sut->update()
                ->addFrom(new Table('offices'))
                ->setValues(['territory' => "'Berlin'"])
                ->addWhere("officeCode = 'oc'");

            $this->assertTrue(PDOHelper::execute($this->pdo, $query));

            // DELETE
            $query = $this->sut->delete()
                ->addFrom(new Table('offices'))
                ->addWhere(new Expr('officeCode = ?', ['abc']));

            $this->assertTrue(PDOHelper::execute($this->pdo, $query));

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
                ->setInto(new Table('offices'))
                ->setColumns('officeCode', 'city', 'phone', 'addressLine1', 'country', 'postalCode', 'territory')
                ->addValues("'abc'", "'Berlin'", "'+49 101 123 4567'", "''", "'Germany'", "'10111'", "'NA'")
                ->addValues("'bcd'", "'Budapest'", "'+36 70 101 1234'", "''", "'Hungary'", "'1011'", "'NA'")
                ->setReturning('*');

            $values = PDOHelper::fetchAll($this->pdo, $query, \PDO::FETCH_ASSOC);

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
                ->addFrom(new Table('offices'))
                ->addWhere(new Expr('officeCode IN (?)', [['abc', 'bcd']]));

            $this->assertTrue(PDOHelper::execute($this->pdo, $query));

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
