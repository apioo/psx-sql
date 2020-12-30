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

## Generation


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

$table = $tableManager->getTable(Acme\Table\News::class);

// returns by default 16 entries from the table ordered by the primary column 
// descending. The default settings can be overriden in the table class
$table->getAll();

// returns 12 entries starting at index 0
$table->getAll(0, 12);

// orders the entries after the column "id" descending
$table->getAll(0, 12, 'id', Sql::SORT_DESC);

// adds a condition to select only the rows where the title contains "foo"
$condition = new Condition();
$condition->like('title', '%foo%');
$table->getAll(0, 12, 'id', Sql::SORT_DESC, $condition);

// adds a blacklist so that the column "password" gets not returned
$table->getAll(0, 12, 'id', Sql::SORT_DESC, null, Fields::blacklist(['password']));

// returns all columns which match the provided condition
$table->getBy(new Condition(['userId', '=', 1]));

// it is also possible to use a magic method which adds a ['userId', '=', 1]
// condition
$table->getByUserId(1);

// returns a single row matching the provided condition
$table->getOneBy(new Condition(['userId', '=', 1]));

// it is also possible to use a magic method
$table->getOneByUserId(1);

// returns a complete row by the primary key
$table->get(1);

// returns the count of entries in the table. It is also possible to provide a
// condition
$table->getCount();

// creates a new row on the table
$table->create([
   'title' => 'foo'
]);

// updates a row. The array must contains the primary key column
$table->update([
    'id'    => 1,
    'title' => 'bar',
]);

// deletes a row. The array must contains the primary key column
$table->delete([
   'id'    => 1,
]);

```

## Table

The following is an example of a table class.

```php
<?php

namespace Acme\Table;

use PSX\Sql\TableAbstract;
use PSX\Sql\TableInterface;

class AcmeTable extends TableAbstract
{
    public function getName()
    {
        return 'acme_table';
    }

    public function getColumns()
    {
        return array(
            'id'     => TableInterface::TYPE_INT | 10 | TableInterface::PRIMARY_KEY | TableInterface::AUTO_INCREMENT,
            'userId' => TableInterface::TYPE_INT | 10,
            'title'  => TableInterface::TYPE_VARCHAR | 32,
            'date'   => TableInterface::TYPE_DATETIME,
        );
    }
}
```

## Views

It is also possible to build view classes which is not based on a specific 
table. Instead you can build complex aggregated result sets.

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

## Generation

The library contains also a Symfony-Command to generate a table class from an
actual table.

```
sql:generate acme_news > AcmeTable.php
```
