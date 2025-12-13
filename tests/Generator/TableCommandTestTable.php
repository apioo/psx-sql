<?php

namespace PSX\Sql\Tests\Generator;

/**
 * @extends \PSX\Sql\TableAbstract<\PSX\Sql\Tests\Generator\TableCommandTestRow>
 */
class TableCommandTestTable extends \PSX\Sql\TableAbstract
{
    public const NAME = 'psx_table_command_test';
    public const COLUMN_ID = 'id';
    public const COLUMN_COL_BIGINT = 'col_bigint';
    public const COLUMN_COL_BINARY = 'col_binary';
    public const COLUMN_COL_BLOB = 'col_blob';
    public const COLUMN_COL_BOOLEAN = 'col_boolean';
    public const COLUMN_COL_DATETIME = 'col_datetime';
    public const COLUMN_COL_DATETIMETZ = 'col_datetimetz';
    public const COLUMN_COL_DATE = 'col_date';
    public const COLUMN_COL_DECIMAL = 'col_decimal';
    public const COLUMN_COL_FLOAT = 'col_float';
    public const COLUMN_COL_INTEGER = 'col_integer';
    public const COLUMN_COL_SMALLINT = 'col_smallint';
    public const COLUMN_COL_TEXT = 'col_text';
    public const COLUMN_COL_TIME = 'col_time';
    public const COLUMN_COL_STRING = 'col_string';
    public const COLUMN_COL_JSON = 'col_json';
    public const COLUMN_COL_GUID = 'col_guid';
    public function getName(): string
    {
        return self::NAME;
    }
    public function getColumns(): array
    {
        return [self::COLUMN_ID => 0x3020000a, self::COLUMN_COL_BIGINT => 0x300000, self::COLUMN_COL_BINARY => 0xc00000, self::COLUMN_COL_BLOB => 0xc00000, self::COLUMN_COL_BOOLEAN => 0x400000, self::COLUMN_COL_DATETIME => 0x800000, self::COLUMN_COL_DATETIMETZ => 0x800000, self::COLUMN_COL_DATE => 0x700000, self::COLUMN_COL_DECIMAL => 0x500000, self::COLUMN_COL_FLOAT => 0x60000a, self::COLUMN_COL_INTEGER => 0x20000a, self::COLUMN_COL_SMALLINT => 0x100000, self::COLUMN_COL_TEXT => 0xb00000, self::COLUMN_COL_TIME => 0x900000, self::COLUMN_COL_STRING => 0xa000ff, self::COLUMN_COL_JSON => 0xf00000, self::COLUMN_COL_GUID => 0x1000024];
    }
    /**
     * @return array<\PSX\Sql\Tests\Generator\TableCommandTestRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findAll(?\PSX\Sql\Condition $condition = null, ?int $startIndex = null, ?int $count = null, ?\PSX\Sql\Tests\Generator\TableCommandTestColumn $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null): array
    {
        return $this->doFindAll($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @return array<\PSX\Sql\Tests\Generator\TableCommandTestRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findBy(\PSX\Sql\Condition $condition, ?int $startIndex = null, ?int $count = null, ?\PSX\Sql\Tests\Generator\TableCommandTestColumn $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null): array
    {
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneBy(\PSX\Sql\Condition $condition): ?\PSX\Sql\Tests\Generator\TableCommandTestRow
    {
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function find(int $id): ?\PSX\Sql\Tests\Generator\TableCommandTestRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('id', $id);
        return $this->doFindOneBy($condition);
    }
    /**
     * @return array<\PSX\Sql\Tests\Generator\TableCommandTestRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findById(int $value, ?int $startIndex = null, ?int $count = null, ?\PSX\Sql\Tests\Generator\TableCommandTestColumn $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null): array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('id', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneById(int $value): ?\PSX\Sql\Tests\Generator\TableCommandTestRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('id', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateById(int $value, \PSX\Sql\Tests\Generator\TableCommandTestRow $record): int
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
     * @return array<\PSX\Sql\Tests\Generator\TableCommandTestRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByColBigint(string $value, ?int $startIndex = null, ?int $count = null, ?\PSX\Sql\Tests\Generator\TableCommandTestColumn $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null): array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('col_bigint', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByColBigint(string $value): ?\PSX\Sql\Tests\Generator\TableCommandTestRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('col_bigint', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByColBigint(string $value, \PSX\Sql\Tests\Generator\TableCommandTestRow $record): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('col_bigint', $value);
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function deleteByColBigint(string $value): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('col_bigint', $value);
        return $this->doDeleteBy($condition);
    }
    /**
     * @return array<\PSX\Sql\Tests\Generator\TableCommandTestRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByColBinary(mixed $value, ?int $startIndex = null, ?int $count = null, ?\PSX\Sql\Tests\Generator\TableCommandTestColumn $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null): array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('col_binary', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByColBinary(mixed $value): ?\PSX\Sql\Tests\Generator\TableCommandTestRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('col_binary', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByColBinary(mixed $value, \PSX\Sql\Tests\Generator\TableCommandTestRow $record): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('col_binary', $value);
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function deleteByColBinary(mixed $value): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('col_binary', $value);
        return $this->doDeleteBy($condition);
    }
    /**
     * @return array<\PSX\Sql\Tests\Generator\TableCommandTestRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByColBlob(mixed $value, ?int $startIndex = null, ?int $count = null, ?\PSX\Sql\Tests\Generator\TableCommandTestColumn $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null): array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('col_blob', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByColBlob(mixed $value): ?\PSX\Sql\Tests\Generator\TableCommandTestRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('col_blob', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByColBlob(mixed $value, \PSX\Sql\Tests\Generator\TableCommandTestRow $record): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('col_blob', $value);
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function deleteByColBlob(mixed $value): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('col_blob', $value);
        return $this->doDeleteBy($condition);
    }
    /**
     * @return array<\PSX\Sql\Tests\Generator\TableCommandTestRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByColBoolean(bool $value, ?int $startIndex = null, ?int $count = null, ?\PSX\Sql\Tests\Generator\TableCommandTestColumn $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null): array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('col_boolean', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByColBoolean(bool $value): ?\PSX\Sql\Tests\Generator\TableCommandTestRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('col_boolean', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByColBoolean(bool $value, \PSX\Sql\Tests\Generator\TableCommandTestRow $record): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('col_boolean', $value);
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function deleteByColBoolean(bool $value): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('col_boolean', $value);
        return $this->doDeleteBy($condition);
    }
    /**
     * @return array<\PSX\Sql\Tests\Generator\TableCommandTestRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByColDatetime(\PSX\DateTime\LocalDateTime $value, ?int $startIndex = null, ?int $count = null, ?\PSX\Sql\Tests\Generator\TableCommandTestColumn $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null): array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('col_datetime', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByColDatetime(\PSX\DateTime\LocalDateTime $value): ?\PSX\Sql\Tests\Generator\TableCommandTestRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('col_datetime', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByColDatetime(\PSX\DateTime\LocalDateTime $value, \PSX\Sql\Tests\Generator\TableCommandTestRow $record): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('col_datetime', $value);
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function deleteByColDatetime(\PSX\DateTime\LocalDateTime $value): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('col_datetime', $value);
        return $this->doDeleteBy($condition);
    }
    /**
     * @return array<\PSX\Sql\Tests\Generator\TableCommandTestRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByColDatetimetz(\PSX\DateTime\LocalDateTime $value, ?int $startIndex = null, ?int $count = null, ?\PSX\Sql\Tests\Generator\TableCommandTestColumn $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null): array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('col_datetimetz', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByColDatetimetz(\PSX\DateTime\LocalDateTime $value): ?\PSX\Sql\Tests\Generator\TableCommandTestRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('col_datetimetz', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByColDatetimetz(\PSX\DateTime\LocalDateTime $value, \PSX\Sql\Tests\Generator\TableCommandTestRow $record): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('col_datetimetz', $value);
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function deleteByColDatetimetz(\PSX\DateTime\LocalDateTime $value): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('col_datetimetz', $value);
        return $this->doDeleteBy($condition);
    }
    /**
     * @return array<\PSX\Sql\Tests\Generator\TableCommandTestRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByColDate(\PSX\DateTime\LocalDate $value, ?int $startIndex = null, ?int $count = null, ?\PSX\Sql\Tests\Generator\TableCommandTestColumn $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null): array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('col_date', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByColDate(\PSX\DateTime\LocalDate $value): ?\PSX\Sql\Tests\Generator\TableCommandTestRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('col_date', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByColDate(\PSX\DateTime\LocalDate $value, \PSX\Sql\Tests\Generator\TableCommandTestRow $record): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('col_date', $value);
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function deleteByColDate(\PSX\DateTime\LocalDate $value): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('col_date', $value);
        return $this->doDeleteBy($condition);
    }
    /**
     * @return array<\PSX\Sql\Tests\Generator\TableCommandTestRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByColDecimal(string $value, ?int $startIndex = null, ?int $count = null, ?\PSX\Sql\Tests\Generator\TableCommandTestColumn $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null): array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('col_decimal', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByColDecimal(string $value): ?\PSX\Sql\Tests\Generator\TableCommandTestRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('col_decimal', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByColDecimal(string $value, \PSX\Sql\Tests\Generator\TableCommandTestRow $record): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('col_decimal', $value);
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function deleteByColDecimal(string $value): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('col_decimal', $value);
        return $this->doDeleteBy($condition);
    }
    /**
     * @return array<\PSX\Sql\Tests\Generator\TableCommandTestRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByColFloat(float $value, ?int $startIndex = null, ?int $count = null, ?\PSX\Sql\Tests\Generator\TableCommandTestColumn $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null): array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('col_float', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByColFloat(float $value): ?\PSX\Sql\Tests\Generator\TableCommandTestRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('col_float', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByColFloat(float $value, \PSX\Sql\Tests\Generator\TableCommandTestRow $record): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('col_float', $value);
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function deleteByColFloat(float $value): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('col_float', $value);
        return $this->doDeleteBy($condition);
    }
    /**
     * @return array<\PSX\Sql\Tests\Generator\TableCommandTestRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByColInteger(int $value, ?int $startIndex = null, ?int $count = null, ?\PSX\Sql\Tests\Generator\TableCommandTestColumn $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null): array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('col_integer', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByColInteger(int $value): ?\PSX\Sql\Tests\Generator\TableCommandTestRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('col_integer', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByColInteger(int $value, \PSX\Sql\Tests\Generator\TableCommandTestRow $record): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('col_integer', $value);
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function deleteByColInteger(int $value): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('col_integer', $value);
        return $this->doDeleteBy($condition);
    }
    /**
     * @return array<\PSX\Sql\Tests\Generator\TableCommandTestRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByColSmallint(int $value, ?int $startIndex = null, ?int $count = null, ?\PSX\Sql\Tests\Generator\TableCommandTestColumn $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null): array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('col_smallint', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByColSmallint(int $value): ?\PSX\Sql\Tests\Generator\TableCommandTestRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('col_smallint', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByColSmallint(int $value, \PSX\Sql\Tests\Generator\TableCommandTestRow $record): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('col_smallint', $value);
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function deleteByColSmallint(int $value): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('col_smallint', $value);
        return $this->doDeleteBy($condition);
    }
    /**
     * @return array<\PSX\Sql\Tests\Generator\TableCommandTestRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByColText(string $value, ?int $startIndex = null, ?int $count = null, ?\PSX\Sql\Tests\Generator\TableCommandTestColumn $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null): array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('col_text', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByColText(string $value): ?\PSX\Sql\Tests\Generator\TableCommandTestRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('col_text', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByColText(string $value, \PSX\Sql\Tests\Generator\TableCommandTestRow $record): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('col_text', $value);
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function deleteByColText(string $value): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('col_text', $value);
        return $this->doDeleteBy($condition);
    }
    /**
     * @return array<\PSX\Sql\Tests\Generator\TableCommandTestRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByColTime(\PSX\DateTime\LocalTime $value, ?int $startIndex = null, ?int $count = null, ?\PSX\Sql\Tests\Generator\TableCommandTestColumn $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null): array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('col_time', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByColTime(\PSX\DateTime\LocalTime $value): ?\PSX\Sql\Tests\Generator\TableCommandTestRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('col_time', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByColTime(\PSX\DateTime\LocalTime $value, \PSX\Sql\Tests\Generator\TableCommandTestRow $record): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('col_time', $value);
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function deleteByColTime(\PSX\DateTime\LocalTime $value): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('col_time', $value);
        return $this->doDeleteBy($condition);
    }
    /**
     * @return array<\PSX\Sql\Tests\Generator\TableCommandTestRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByColString(string $value, ?int $startIndex = null, ?int $count = null, ?\PSX\Sql\Tests\Generator\TableCommandTestColumn $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null): array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('col_string', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByColString(string $value): ?\PSX\Sql\Tests\Generator\TableCommandTestRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('col_string', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByColString(string $value, \PSX\Sql\Tests\Generator\TableCommandTestRow $record): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('col_string', $value);
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function deleteByColString(string $value): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('col_string', $value);
        return $this->doDeleteBy($condition);
    }
    /**
     * @return array<\PSX\Sql\Tests\Generator\TableCommandTestRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByColJson(mixed $value, ?int $startIndex = null, ?int $count = null, ?\PSX\Sql\Tests\Generator\TableCommandTestColumn $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null): array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('col_json', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByColJson(mixed $value): ?\PSX\Sql\Tests\Generator\TableCommandTestRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('col_json', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByColJson(mixed $value, \PSX\Sql\Tests\Generator\TableCommandTestRow $record): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('col_json', $value);
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function deleteByColJson(mixed $value): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('col_json', $value);
        return $this->doDeleteBy($condition);
    }
    /**
     * @return array<\PSX\Sql\Tests\Generator\TableCommandTestRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByColGuid(string $value, ?int $startIndex = null, ?int $count = null, ?\PSX\Sql\Tests\Generator\TableCommandTestColumn $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null): array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('col_guid', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByColGuid(string $value): ?\PSX\Sql\Tests\Generator\TableCommandTestRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('col_guid', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByColGuid(string $value, \PSX\Sql\Tests\Generator\TableCommandTestRow $record): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('col_guid', $value);
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function deleteByColGuid(string $value): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('col_guid', $value);
        return $this->doDeleteBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function create(\PSX\Sql\Tests\Generator\TableCommandTestRow $record): int
    {
        return $this->doCreate($record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function update(\PSX\Sql\Tests\Generator\TableCommandTestRow $record): int
    {
        return $this->doUpdate($record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateBy(\PSX\Sql\Condition $condition, \PSX\Sql\Tests\Generator\TableCommandTestRow $record): int
    {
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function delete(\PSX\Sql\Tests\Generator\TableCommandTestRow $record): int
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
    protected function newRecord(array $row): \PSX\Sql\Tests\Generator\TableCommandTestRow
    {
        return \PSX\Sql\Tests\Generator\TableCommandTestRow::from($row);
    }
}