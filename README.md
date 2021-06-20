# qb

QB is a generic query build which currently supports the base commands of MySQL and PostgreSQL.

QB aims to support over 95% of use cases, but is unlikely to ever support 100%. Pull requests for supporting other databases and commands are welcome.

Written because most other projects do not support joining tables over other joined tables. (Many-to-many support)

### Example

```php
use QB\Generic\Clause\Column;
use QB\Generic\Expr\Expr;
use QB\MySQL\Statement\Select;

$columnQuery = (new Select())
    ->addFrom('quix')
    ->addColumns('b')
    ->addWhere(new Expr('id = ?', [7]));

$columnExpr = new Expr('NOW()');

$unionQuery = (new Select())
    ->addFrom('baz')
    ->addColumns('b', 'f');

$sql =  (string)(new Select())
    ->addFrom('foo', 'bar')
    ->addModifier('DISTINCT')
    ->addColumns('COUNT(DISTINCT baz) AS baz_count', new Column($columnQuery, 'quix_b'))
    ->addColumns(new Column($columnExpr, 'now'))
    ->addColumn('bar.id', 'bar_id')
    ->addInnerJoin('quix', 'foo.id = q.foo_id', 'q')
    ->addWhere('foo.bar = "foo-bar"', new Expr('bar.foo = ?', ['bar-foo']))
    ->addGroupBy('q.foo_id', new Expr('q.bar.id'))
    ->setGroupWithRollup()
    ->addHaving('baz_count > 0')
    ->addOrderBy('baz_count', 'ASC')
    ->setLimit(10)
    ->setOffset(20)
    ->addLock(Select::LOCK_FOR_UPDATE, Select::LOCK_NOWAIT)
    ->addUnion($unionQuery);

// SELECT DISTINCT COUNT(DISTINCT baz) AS baz_count, (SELECT b FROM quix WHERE id = ?) AS quix_b, NOW() AS now, bar.id AS bar_id
// FROM foo, bar
// INNER JOIN quix AS q ON foo.id = q.foo_id
// WHERE foo.bar = "foo-bar" AND bar.foo = ?
// GROUP BY q.foo_id, q.bar.id WITH ROLLUP
// HAVING baz_count > 0
// ORDER BY baz_count ASC
// LIMIT 20, 10
// FOR UPDATE NOWAIT
// UNION
// SELECT b, f
// FROM baz
```
