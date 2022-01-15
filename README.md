
# Sql

## About

In traditional ORMs you write a class add specific metadata and generate based on this class your tables, this means our
source code defines how a table should look. This library thinks the other way around (database first), this means you
first build your database schema i.e. through a tool like doctrine migrations and then you can use this library to
automatically generate all repository and model classes based on the table schema. This has the great advantage that we
can generate completely typed repositories. We automatically generate a class for each row (entity) and a repository
which accepts this row. This concept is not new and the Java world has i.e. jOOQ which also follows this idea. It of
course means also that you need to regenerate your classes if you change your schema.

## Basic usage

The following are basic examples how you can work with a table class

```php
<?php

use PSX\Sql\Condition;
use PSX\Sql\Fields;
use PSX\Sql\Sql;
use PSX\Sql\TableManager;

$connection   = null; // a doctrine DBAL connection
$tableManager = new TableManager($connection);

/** @var \PSX\Sql\Tests\Generator\SqlTableTestTable $table */
$table = $tableManager->getTable(\PSX\Sql\Tests\Generator\SqlTableTestTable::class);

// returns by default 16 entries from the table ordered by the primary column descending
$table->findAll();

// returns 12 entries starting at index 0
$table->findAll(startIndex: 0, count: 12);

// orders the entries after the column "id" descending
$table->findAll(startIndex: 0, count: 12, sortBy: 'id', sortOrder: Sql::SORT_DESC);

// returns all rows which match the specified title
$table->findByTitle('foo%');

// returns a row by the primary key
$table->find(1);

// returns the count of entries in the table. It is also possible to provide a condition
$table->getCount();

// creates a new row
$row = new \PSX\Sql\Tests\Generator\SqlTableTestRow();
$row->setTitle('foo');
$table->create($row);

// updates a row
$row = $table->find(1);
$row->setTitle('bar');
$table->update($row);

// deletes a row
$row = $table->find(1);
$table->delete($row);

```

## Table

The following is an example of a generated table class.

```php
<?php

namespace PSX\Sql\Tests\Generator;

class SqlTableTestTable extends \PSX\Sql\TableAbstract
{
    public const NAME = 'psx_sql_table_test';
    public const COLUMN_ID = 'id';
    public const COLUMN_TITLE = 'title';
    public const COLUMN_DATE = 'date';
    public function getName() : string
    {
        return self::NAME;
    }
    public function getColumns() : array
    {
        return array(self::COLUMN_ID => 0x3020000a, self::COLUMN_TITLE => 0xa00020, self::COLUMN_DATE => 0x800000);
    }
    /**
     * @return \PSX\Sql\Tests\Generator\SqlTableTestRow[]
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findAll(?\PSX\Sql\Condition $condition = null, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null, ?\PSX\Sql\Fields $fields = null) : iterable
    {
        return $this->doFindAll($condition, $startIndex, $count, $sortBy, $sortOrder, $fields);
    }
    /**
     * @return \PSX\Sql\Tests\Generator\SqlTableTestRow[]
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findBy(\PSX\Sql\Condition $condition, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null, ?\PSX\Sql\Fields $fields = null) : iterable
    {
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder, $fields);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneBy(\PSX\Sql\Condition $condition, ?\PSX\Sql\Fields $fields = null) : ?\PSX\Sql\Tests\Generator\SqlTableTestRow
    {
        return $this->doFindOneBy($condition, $fields);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function find(int $id) : ?\PSX\Sql\Tests\Generator\SqlTableTestRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('id', $id);
        return $this->doFindOneBy($condition);
    }
    /**
     * @return \PSX\Sql\Tests\Generator\SqlTableTestRow[]
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findById(int $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('id', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneById(int $value) : ?\PSX\Sql\Tests\Generator\SqlTableTestRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('id', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @return \PSX\Sql\Tests\Generator\SqlTableTestRow[]
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByTitle(string $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('title', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByTitle(string $value) : ?\PSX\Sql\Tests\Generator\SqlTableTestRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('title', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @return \PSX\Sql\Tests\Generator\SqlTableTestRow[]
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByDate(\DateTime $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('date', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByDate(\DateTime $value) : ?\PSX\Sql\Tests\Generator\SqlTableTestRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('date', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function create(\PSX\Sql\Tests\Generator\SqlTableTestRow $record) : int
    {
        return $this->doCreate($record);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function update(\PSX\Sql\Tests\Generator\SqlTableTestRow $record) : int
    {
        return $this->doUpdate($record);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function delete(\PSX\Sql\Tests\Generator\SqlTableTestRow $record) : int
    {
        return $this->doDelete($record);
    }
    protected function newRecord(array $row) : \PSX\Sql\Tests\Generator\SqlTableTestRow
    {
        return new \PSX\Sql\Tests\Generator\SqlTableTestRow($row);
    }
}
```

## Row

The following is an example of a generated table row.

```php
<?php

namespace PSX\Sql\Tests\Generator;

class SqlTableTestRow extends \PSX\Record\Record
{
    public function setId(?int $id) : void
    {
        $this->setProperty('id', $id);
    }
    public function getId() : ?int
    {
        return $this->getProperty('id');
    }
    public function setTitle(?string $title) : void
    {
        $this->setProperty('title', $title);
    }
    public function getTitle() : ?string
    {
        return $this->getProperty('title');
    }
    public function setDate(?\DateTime $date) : void
    {
        $this->setProperty('date', $date);
    }
    public function getDate() : ?\DateTime
    {
        return $this->getProperty('date');
    }
}
```

## Views

It is also possible to build view classes which do not work on a specific table but instead
can combine multiple tables to produce a complex result.

```php
<?php

namespace Acme\View;

use PSX\Sql\Reference;
use PSX\Sql\ViewAbstract;

class AcmeView extends ViewAbstract
{
    public function getNestedResult()
    {
        $definition = $this->doCollection([$this->getTable(HandlerCommentTable::class), 'findAll'], [], [
            'id' => $this->fieldInteger('id'),
            'title' => $this->fieldCallback('title', function($title){
                return ucfirst($title);
            }),
            'author' => [
                'id' => $this->fieldFormat('userId', 'urn:profile:%s'),
                'date' => $this->fieldDateTime('date'),
            ],
            'note' => $this->doEntity([$this->getTable(TableCommandTestTable::class), 'findOneById'], [new Reference('id')], [
                'comments' => true,
                'title' => 'col_text',
            ]),
            'count' => $this->doValue('SELECT COUNT(*) AS cnt FROM psx_handler_comment', [], $this->fieldInteger('cnt')),
            'tags' => $this->doColumn('SELECT date FROM psx_handler_comment', [], 'date'),
        ]);

        return $this->build($definition);
    }
}
```

The `getNestedResult` method would produce the following json response

```json
[
  {
    "id": 4,
    "title": "Blub",
    "author": {
      "id": "urn:profile:3",
      "date": "2013-04-29T16:56:32Z"
    },
    "count": 4,
    "tags": [
      "2013-04-29 16:56:32",
      "2013-04-29 16:56:32",
      "2013-04-29 16:56:32",
      "2013-04-29 16:56:32"
    ]
  },
  ...
]
```
