
# Sql

This library generates type-safe PHP classes from your database tables and thus allows you to interact with your
database in a complete type-safe way.

## About

In traditional ORMs you write a class add specific metadata and generate based on this class your tables, this means our
source code defines how a table should look. This library thinks the other way around (database first), this means you
first build your database schema i.e. through a tool like doctrine migrations and then you can use this library to
automatically generate all repository and model classes based on the table schema. This has the great advantage that we
can generate completely typed repositories. We automatically generate a class for each row (entity) and a repository
which accepts this row. This concept is not new and the Java world has i.e. jOOQ which also follows this idea. It of
course means also that you need to regenerate your classes if you change your schema.

## Generate

To generate the table and row classes you can either integrate the `PSX\Command\GenerateCommand` into your Symfony
console app or you can also do this programmatically through the `PSX\Sql\Generator` class s.

```php
<?php

use PSX\Sql\Generator\Generator;

$connection = null; // a doctrine DBAL connection
$target = __DIR__;

$generator = new Generator($connection, 'Acme\\Table');
foreach ($generator->generate() as $className => $source) {
    file_put_contents($target . '/' . $className . '.php', '<?php' . "\n\n" . $source);
}

```

## Basic usage

The following are basic examples how you can work with a generated table class.

```php
<?php

use PSX\Sql\Condition;
use PSX\Sql\OrderBy;
use PSX\Sql\TableManager;
use PSX\Sql\Tests\Generator\SqlTableTestTable;
use PSX\Sql\Tests\Generator\SqlTableTestColumn;
use PSX\Sql\Tests\Generator\SqlTableTestRow;

$connection   = null; // a doctrine DBAL connection
$tableManager = new TableManager($connection);

/** @var SqlTableTestTable $table */
$table = $tableManager->getTable(SqlTableTestTable::class);

// returns by default 16 entries from the table ordered by the primary column descending
$table->findAll();

// returns 12 entries starting at index 0
$table->findAll(startIndex: 0, count: 12);

// orders the entries after the column "id" descending
$table->findAll(startIndex: 0, count: 12, sortBy: SqlTableTestColumn::ID, sortOrder: OrderBy::DESC);

// returns all rows which match the specified title
$table->findByTitle('foo%');

// returns a row by the primary key
$table->find(1);

// returns the count of entries in the table. It is also possible to provide a condition
$table->getCount();

// creates a new row
$row = new SqlTableTestRow();
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

/**
 * @extends \PSX\Sql\TableAbstract<\PSX\Sql\Tests\Generator\SqlTableTestRow>
 */
class SqlTableTestTable extends \PSX\Sql\TableAbstract
{
    public const NAME = 'psx_sql_table_test';
    public const COLUMN_ID = 'id';
    public const COLUMN_TITLE = 'title';
    public const COLUMN_DATE = 'date';
    public function getName(): string
    {
        return self::NAME;
    }
    public function getColumns(): array
    {
        return [self::COLUMN_ID => 0x3020000a, self::COLUMN_TITLE => 0xa00020, self::COLUMN_DATE => 0x800000];
    }
    /**
     * @return array<\PSX\Sql\Tests\Generator\SqlTableTestRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findAll(?\PSX\Sql\Condition $condition = null, ?int $startIndex = null, ?int $count = null, ?\PSX\Sql\Tests\Generator\SqlTableTestColumn $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null): array
    {
        return $this->doFindAll($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @return array<\PSX\Sql\Tests\Generator\SqlTableTestRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findBy(\PSX\Sql\Condition $condition, ?int $startIndex = null, ?int $count = null, ?\PSX\Sql\Tests\Generator\SqlTableTestColumn $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null): array
    {
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneBy(\PSX\Sql\Condition $condition): ?\PSX\Sql\Tests\Generator\SqlTableTestRow
    {
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function find(int $id): ?\PSX\Sql\Tests\Generator\SqlTableTestRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('id', $id);
        return $this->doFindOneBy($condition);
    }
    /**
     * @return array<\PSX\Sql\Tests\Generator\SqlTableTestRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findById(int $value, ?int $startIndex = null, ?int $count = null, ?\PSX\Sql\Tests\Generator\SqlTableTestColumn $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null): array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('id', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneById(int $value): ?\PSX\Sql\Tests\Generator\SqlTableTestRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('id', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateById(int $value, \PSX\Sql\Tests\Generator\SqlTableTestRow $record): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('id', $value);
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function deleteById(int $value): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('id', $value);
        return $this->doDeleteBy($condition);
    }
    /**
     * @return array<\PSX\Sql\Tests\Generator\SqlTableTestRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByTitle(string $value, ?int $startIndex = null, ?int $count = null, ?\PSX\Sql\Tests\Generator\SqlTableTestColumn $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null): array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('title', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByTitle(string $value): ?\PSX\Sql\Tests\Generator\SqlTableTestRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('title', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByTitle(string $value, \PSX\Sql\Tests\Generator\SqlTableTestRow $record): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('title', $value);
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function deleteByTitle(string $value): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('title', $value);
        return $this->doDeleteBy($condition);
    }
    /**
     * @return array<\PSX\Sql\Tests\Generator\SqlTableTestRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByDate(\PSX\DateTime\LocalDateTime $value, ?int $startIndex = null, ?int $count = null, ?\PSX\Sql\Tests\Generator\SqlTableTestColumn $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null): array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('date', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByDate(\PSX\DateTime\LocalDateTime $value): ?\PSX\Sql\Tests\Generator\SqlTableTestRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('date', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByDate(\PSX\DateTime\LocalDateTime $value, \PSX\Sql\Tests\Generator\SqlTableTestRow $record): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('date', $value);
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function deleteByDate(\PSX\DateTime\LocalDateTime $value): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('date', $value);
        return $this->doDeleteBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function create(\PSX\Sql\Tests\Generator\SqlTableTestRow $record): int
    {
        return $this->doCreate($record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function update(\PSX\Sql\Tests\Generator\SqlTableTestRow $record): int
    {
        return $this->doUpdate($record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateBy(\PSX\Sql\Condition $condition, \PSX\Sql\Tests\Generator\SqlTableTestRow $record): int
    {
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function delete(\PSX\Sql\Tests\Generator\SqlTableTestRow $record): int
    {
        return $this->doDelete($record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function deleteBy(\PSX\Sql\Condition $condition): int
    {
        return $this->doDeleteBy($condition);
    }
    /**
     * @param array<string, mixed> $row
     */
    protected function newRecord(array $row): \PSX\Sql\Tests\Generator\SqlTableTestRow
    {
        return \PSX\Sql\Tests\Generator\SqlTableTestRow::from($row);
    }
}
```

## Row

The following is an example of a generated table row.

```php
<?php

namespace PSX\Sql\Tests\Generator;

class SqlTableTestRow implements \JsonSerializable, \PSX\Record\RecordableInterface
{
    private ?int $id = null;
    private ?string $title = null;
    private ?\PSX\DateTime\LocalDateTime $date = null;
    public function setId(int $id): void
    {
        $this->id = $id;
    }
    public function getId(): int
    {
        return $this->id ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "id" was provided');
    }
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }
    public function getTitle(): string
    {
        return $this->title ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "title" was provided');
    }
    public function setDate(\PSX\DateTime\LocalDateTime $date): void
    {
        $this->date = $date;
    }
    public function getDate(): \PSX\DateTime\LocalDateTime
    {
        return $this->date ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "date" was provided');
    }
    public function toRecord(): \PSX\Record\RecordInterface
    {
        /** @var \PSX\Record\Record<mixed> $record */
        $record = new \PSX\Record\Record();
        $record->put('id', $this->id);
        $record->put('title', $this->title);
        $record->put('date', $this->date);
        return $record;
    }
    public function jsonSerialize(): object
    {
        return (object) $this->toRecord()->getAll();
    }
    public static function from(array|\ArrayAccess $data): self
    {
        $row = new self();
        $row->id = isset($data['id']) && is_int($data['id']) ? $data['id'] : null;
        $row->title = isset($data['title']) && is_string($data['title']) ? $data['title'] : null;
        $row->date = isset($data['date']) && $data['date'] instanceof \DateTimeInterface ? \PSX\DateTime\LocalDateTime::from($data['date']) : null;
        return $row;
    }
}
```

## Column

The following is an example of a generated table column.

```php
<?php

namespace PSX\Sql\Tests\Generator;

enum SqlTableTestColumn : string implements \PSX\Sql\ColumnInterface
{
    case ID = \PSX\Sql\Tests\Generator\SqlTableTestTable::COLUMN_ID;
    case TITLE = \PSX\Sql\Tests\Generator\SqlTableTestTable::COLUMN_TITLE;
    case DATE = \PSX\Sql\Tests\Generator\SqlTableTestTable::COLUMN_DATE;
}
```
