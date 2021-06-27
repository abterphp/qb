# qb

[![Github Actions Build](https://github.com/abterphp/qb/actions/workflows/ci.yml/badge.svg?branch=main)](https://github.com/abterphp/qb/actions)
[![Scrutinizer Quality](https://scrutinizer-ci.com/g/abterphp/qb/badges/quality-score.png?b=main)](https://scrutinizer-ci.com/g/abterphp/qb/?branch=main)
[![Scrutinizer Build](https://scrutinizer-ci.com/g/abterphp/qb/badges/build.png?b=main)](https://scrutinizer-ci.com/g/abterphp/qb/build-status/main)
[![Scrutinizer Coverage](https://scrutinizer-ci.com/g/abterphp/qb/badges/coverage.png?b=main)](https://scrutinizer-ci.com/g/abterphp/qb/?branch=main)
[![Code Climate Quality](https://api.codeclimate.com/v1/badges/de5438c64f64b2bba149/maintainability)](https://codeclimate.com/github/abterphp/qb/maintainability)
[![Code Climate Coverage](https://api.codeclimate.com/v1/badges/de5438c64f64b2bba149/test_coverage)](https://codeclimate.com/github/abterphp/qb/test_coverage)

QB is a generic query build which currently supports the base commands of MySQL and PostgreSQL.

 - QB aims to support over 95% of use cases, not 100% 
 - There's still a bit to go for PostgreSQL support, MySQL should already be there. 
 - Pull requests for supporting other databases and commands are welcome.
 - Written because most other projects do not support joining tables over other joined tables. (Many-to-many support)

### Examples

#### MySQL SELECT with union

```php
use QB\Generic\Clause\Column;
use QB\Generic\Expr\Expr;
use QB\MySQL\Clause\CombiningQuery;
use QB\MySQL\Clause\Lock;
use QB\MySQL\Statement\Select;

$columnQuery = (new Select())
    ->addFrom('quix')
    ->addColumns('b')
    ->addWhere(new Expr('id = ?', [7]));

$columnExpr = new Expr('NOW()');

$unionQuery = (new Select())
    ->addFrom('baz')
    ->addColumns('b', 'f');

$sql = (string)(new Select())
    ->addFrom('foo', 'bar')
    ->addModifier('DISTINCT')
    ->addColumns('COUNT(DISTINCT baz) AS baz_count', new Column($columnQuery, 'quix_b'))
    ->addColumns(new Column($columnExpr, 'now'))
    ->addColumn('bar.id', 'bar_id')
    ->addInnerJoin('quix', 'foo.id = q.foo_id', 'q')
    ->addWhere('foo.bar = "foo-bar"', new Expr('bar.foo = ?', ['bar-foo']))
    ->addWhere(new Expr('bar.foo IN (?)', [['bar', 'foo']]))
    ->addGroupBy('q.foo_id', new Expr('q.bar.id'))
    ->setGroupWithRollup()
    ->addHaving('baz_count > 0')
    ->addOrderBy('baz_count', 'ASC')
    ->setLimit(10)
    ->setOffset(20)
    ->setLock(new Lock(Lock::FOR_UPDATE, ['foo'], Lock::MODIFIER_NOWAIT))
    ->addUnion($unionQuery, CombiningQuery::MODIFIER_DISTINCT);

// SELECT DISTINCT COUNT(DISTINCT baz) AS baz_count, (SELECT b FROM quix WHERE id = ?) AS quix_b, NOW() AS now, bar.id AS bar_id
// FROM foo, bar
// INNER JOIN quix AS q ON foo.id = q.foo_id
// WHERE foo.bar = "foo-bar" AND bar.foo = ? AND bar.foo IN (?, ?)
// GROUP BY q.foo_id, q.bar.id WITH ROLLUP
// HAVING baz_count > 0
// ORDER BY baz_count ASC
// LIMIT 20, 10
// FOR UPDATE OF foo NOWAIT
// UNION DISTINCT
// SELECT b, f
// FROM baz
```

#### PostgreSQL INSERT with UPDATE ON CONFLICT AND RETURNING

```php
use QB\Generic\Clause\Table;

// INSERT
$query = $this->getSut('offices')
    ->setInto(new Table('offices'))
    ->addColumns('officeCode', 'city', 'phone', 'addressLine1', 'country', 'postalCode', 'territory')
    ->addValues('abc', 'Berlin', '+49 101 123 4567', '', 'Germany', '10111', 'NA')
    ->addValues('bcd', 'Budapest', '+36 70 101 1234', '', 'Hungary', '1011', 'NA')
    ->setOnConflict('officeCode', 'city')
    ->setDoUpdate('officeCode = EXCLUDED.officeCode', 'city = EXCLUDED.city')
    ->setReturning('*');
    
// INSERT INTO offices (officeCode, city, phone, addressLine1, country, postalCode, territory)
// VALUES (?, ?, ?, ?, ?, ?, ?),
// (?, ?, ?, ?, ?, ?, ?)
// ON CONFLICT (officeCode, city) DO UPDATE
// SET officeCode = EXCLUDED.officeCode, city = EXCLUDED.city
// RETURNING *
```