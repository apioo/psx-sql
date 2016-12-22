PSX Sql
===

## About

The SQL library helps to manage data from relational databases. It is designed 
to use raw SQL queries and to build nested results. It has a simple table 
concept where a table class represents a table on your database. You can simply 
add custom methods to a table class which can issue complex SQL queries.

## Basic usage

The following are basic examples how you can work with a table class

```php
$connection   = null; // a doctrine DBAL connection
$tableManager = new TableManager($connection);

$table = $tableManager->getTable('Acme\Table\News');

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

// returns all columsn which match the provided condition
$table->getBy(new Condition('userId', '=', 1));

// it is also possible to use a magic method
$table->getByUserId(1);

// returns a single row matching the provided condition
$table->getOneBy(new Condition('userId', '=', 1));

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

    /**
     * In this method we use a custom query and specify the types of the return
     * columns
     */
    public function getCustomQuery($title = null)
    {
        $params = [];
        $sql = '    SELECT acme_table.title,
                           acme_table.insertDate
                      FROM acme_table 
                INNER JOIN acme_news 
                        ON acme_table.newsId = acme_news.id 
                     WHERE acme_table.insertDate > DATE_SUB(NOW(), INTERVAL 1 DAY)';

        if (!empty($title)) {
            $sql.= ' AND title LIKE :title';
            $params['title'] = '%' . $title . '%';
        }

        return $this->project($sql, $params, [
            self::TYPE_VARCHAR,
            self::TYPE_DATETIME,
        ]);
    }

    /**
     * Example howto build a nested result based on different tables
     */
    public function getNestedResult()
    {
        $definition = [
            'totalEntries' => $this->getCount(),
            'entries' => $this->doCollection('SELECT id, authorId, title, createDate FROM news ORDER BY createDate DESC', [], [
                'id' => $this->type('id', TableInterface::TYPE_INT),
                'title' => $this->callback('title', function($title){
                    return ucfirst($title);
                }),
                'isNew' => $this->value(true),
                'author' => $this->doEntity('SELECT name, uri FROM author WHERE id = :id', ['id' => new Reference('authorId')], [
                    'displayName' => 'name',
                    'uri' => 'uri',
                ]),
                'date' => $this->dateTime('createDate'),
                'links' => [
                    'self' => $this->replace('http://foobar.com/news/{id}'),
                ]
            ])
        ];

        return $this->build($definition));
    }
}
```

The getNestedResult method could produce the following json response

```json
{
    "totalEntries": 2,
    "entries": [
        {
            "id": 1,
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
            "id": 2,
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
