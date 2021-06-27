<?php

declare(strict_types=1);

namespace QB\Tests;

use PHPUnit\Framework\TestCase;
use QB\Generic\Clause\Column;
use QB\Generic\Clause\Table;
use QB\Generic\Expr\Expr;
use QB\MySQL\Factory\Factory;
use QB\MySQL\Statement\Select;

class MySQLTest extends TestCase
{
    /** @var Factory */
    protected Factory $sut;

    protected \PDO $pdo;

    public function setUp(): void
    {
        if (!array_key_exists('MYSQL_USER', $_ENV)) {
            $this->markTestSkipped('no db');
        }

        $this->sut = new Factory();

        $dns      = sprintf(
            'mysql:dbname=%s;host=%s',
            $_ENV['MYSQL_DATABASE'],
            'mysql'
        );
        $username = $_ENV['MYSQL_USER'];
        $password = $_ENV['MYSQL_PASSWORD'];
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

        $customerTypeColumn = new Column(new Expr("'customer'"), 'type');
        $employeeTypeColumn = new Column(new Expr("'employee'"), 'type');

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
            ->addOuterOrderBy('type', 'DESC')
            ->addOuterOrderBy('lastName')
            ->setOuterLimit($limit);

        $statement = $this->pdo->prepare((string)$query);
        foreach ($query->getParams() as $k => $v) {
            if (is_numeric($k)) {
                $statement->bindParam($k + 1, $v[0], $v[1]);
            } else {
                $statement->bindParam($k, $v[0], $v[1]);
            }
        }

        $statement->execute();

        $this->assertCount($limit, $statement->fetchAll(\PDO::FETCH_ASSOC));
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
            ->addFrom('customers')
            ->addModifier(Select::SQL_CALC_FOUND_ROWS)
            ->addColumns('customerName')
            ->setLimit($limit);

        $statement = $this->pdo->query($sql);

        $this->assertCount($limit, $statement->fetchAll());

        $sql = (string)$this->sut->select()
            ->addColumn(new Expr('FOUND_ROWS()'));

        $statement = $this->pdo->query($sql);

        $this->assertGreaterThan($limit, (int)$statement->fetchColumn());
    }

    public function testInsertUpdateDelete()
    {
        try {
            $this->pdo->exec('BEGIN');

            // INSERT
            $query = $this->sut->insert()
                ->setInto(new Table('offices'))
                ->addColumns('officeCode', 'city', 'phone', 'addressLine1', 'country', 'postalCode', 'territory')
                ->addValues('abc', 'Berlin', '+49 101 123 4567', '', 'Germany', '10111', 'NA');

            $statement = $this->pdo->prepare((string)$query);

            $result = $statement->execute($query->getValues());
            $this->assertTrue($result);

            // UPDATE
            $query = $this->sut->update()
                ->addFrom(new Table('offices'))
                ->setValues(['territory' => 'Berlin'])
                ->addWhere('officeCode = \'oc\'');

            $num    = 1;
            $sql    = (string)$query;
            $values = $query->getValues();
            $params = $query->getParams();

            $statement = $this->pdo->prepare($sql);
            foreach ($values as $value) {
                $statement->bindParam($num++, $value);
            }
            foreach ($params as $k => $v) {
                if (is_numeric($k)) {
                    $statement->bindParam($num++, $v[0], $v[1]);
                } else {
                    $statement->bindParam($k, $v[0], $v[1]);
                }
            }
            $result = $statement->execute($values);
            $this->assertTrue($result);

            // DELETE
            $query = $this->sut->delete()
                ->addFrom(new Table('offices'))
                ->addWhere(new Expr('officeCode = ?', ['abc']));

            $sql    = (string)$query;
            $params = $query->getParams();

            $statement = $this->pdo->prepare($sql);
            foreach ($params as $k => $v) {
                if (is_numeric($k)) {
                    $statement->bindParam($k + 1, $v[0], $v[1]);
                } else {
                    $statement->bindParam($k, $v[0], $v[1]);
                }
            }
            $result = $statement->execute();
            $this->assertTrue($result);

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
