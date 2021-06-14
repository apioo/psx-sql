PSX Sql
===

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
$table->findAll(0, 12);

// orders the entries after the column "id" descending
$table->findAll(0, 12, 'id', Sql::SORT_DESC);

// adds a condition to select only the rows where the title contains "foo"
$table->findByTitle('%foo%', 0, 12, 'id', Sql::SORT_DESC);

// returns a complete row by the primary key
$table->findOneById(1);

// returns a all rows which match the specified title
$table->findByTitle('foo%');

// returns the count of entries in the table. It is also possible to provide a condition
$table->getCount();

// creates a new row
$row = new \PSX\Sql\Tests\Generator\SqlTableTestRow();
$row->setTitle('foo');
$table->create($row);

// updates a row
$row = new \PSX\Sql\Tests\Generator\SqlTableTestRow();
$row->setId(1);
$row->setTitle('bar');
$table->update($row);

// deletes a row
$row = new \PSX\Sql\Tests\Generator\SqlTableTestRow();
$row->setId(1);
$table->delete($row);

```

## Table

The following is an example of a generated table class.

```php
<?php

namespace PSX\Sql\Tests\Generator;

class SqlTableTestTable extends \PSX\Sql\TableAbstract
{
    public const COLUMN_ID = 'id';
    public const COLUMN_TITLE = 'title';
    public const COLUMN_DATE = 'date';
    public function getName()
    {
        return 'psx_sql_table_test';
    }
    public function getColumns()
    {
        return array(self::COLUMN_ID => 0x30200000, self::COLUMN_TITLE => 0xa00020, self::COLUMN_DATE => 0x800000);
    }
    /**
     * @return \PSX\Sql\Tests\Generator\SqlTableTestRow[]
     */
    public function findAll(?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null)
    {
        return $this->getAll($startIndex, $count, $sortBy, $sortOrder, null, null);
    }
    /**
     * @return \PSX\Sql\Tests\Generator\SqlTableTestRow[]
     */
    public function findById(int $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null)
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('id', $value);
        return $this->getBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @return \PSX\Sql\Tests\Generator\SqlTableTestRow
     */
    public function findOneById(int $value)
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('id', $value);
        return $this->getOneBy($condition, null);
    }
    /**
     * @return \PSX\Sql\Tests\Generator\SqlTableTestRow[]
     */
    public function findByTitle(string $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null)
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('title', $value);
        return $this->getBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @return \PSX\Sql\Tests\Generator\SqlTableTestRow
     */
    public function findOneByTitle(string $value)
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('title', $value);
        return $this->getOneBy($condition, null);
    }
    /**
     * @return \PSX\Sql\Tests\Generator\SqlTableTestRow[]
     */
    public function findByDate(\DateTime $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null)
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('date', $value);
        return $this->getBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @return \PSX\Sql\Tests\Generator\SqlTableTestRow
     */
    public function findOneByDate(\DateTime $value)
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('date', $value);
        return $this->getOneBy($condition, null);
    }
    protected function getRecordClass() : string
    {
        return '\\PSX\\Sql\\Tests\\Generator\\SqlTableTestRow';
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

It is also possible to build view classes which is not based on a specific 
table.

```php
<?php

namespace Acme\View;

use PSX\Sql\Reference;
use PSX\Sql\ViewAbstract;

class AcmeView extends ViewAbstract
{
    /**
     * Example howto build a nested result based on different tables. It 
     * contains also some field tranformations
     */
    public function getNestedResult()
    {
        $definition = [
            'totalEntries' => $this->getCount(),
            'entries' => $this->doCollection('SELECT id, authorId, title, createDate FROM news ORDER BY createDate DESC', [], [
                'entryId' => 'id',
                'title' => $this->fieldCallback('title', function($title){
                    return ucfirst($title);
                }),
                'isNew' => $this->fieldValue(true),
                'author' => $this->doEntity('SELECT name, uri FROM author WHERE id = :id', ['id' => new Reference('authorId')], [
                    'displayName' => 'name',
                    'uri' => 'uri',
                ]),
                'date' => $this->fieldDateTime('createDate'),
                'links' => [
                    'self' => $this->fieldReplace('http://foobar.com/news/{id}'),
                ]
            ])
        ];

        return $this->build($definition);
    }
}
```

The `getNestedResult` method could produce the following json response

```json
{
    "totalEntries": 2,
    "entries": [
        {
            "entryId": 1,
            "title": "Foo",
            "isNew": true,
            "author": {
                "displayName": "Foo Bar",
                "uri": "http:\/\/phpsx.org"
            },
            "date": "2016-03-01T00:00:00+01:00",
            "links": {
                "self": "http:\/\/foobar.com\/news\/1"
            }
        },
        {
            "entryId": 2,
            "title": "Bar",
            "isNew": true,
            "author": {
                "displayName": "Foo Bar",
                "uri": "http:\/\/phpsx.org"
            },
            "date": "2016-03-01T00:00:00+01:00",
            "links": {
                "self": "http:\/\/foobar.com\/news\/2"
            }
        }
    ]
}
```
