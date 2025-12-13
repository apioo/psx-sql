<?php

namespace PSX\Sql\Tests\Generator;

class TableCommandTestRow implements \JsonSerializable, \PSX\Record\RecordableInterface
{
    private ?int $id = null;
    private ?string $colBigint = null;
    private mixed $colBinary = null;
    private mixed $colBlob = null;
    private ?bool $colBoolean = null;
    private ?\PSX\DateTime\LocalDateTime $colDatetime = null;
    private ?\PSX\DateTime\LocalDateTime $colDatetimetz = null;
    private ?\PSX\DateTime\LocalDate $colDate = null;
    private ?string $colDecimal = null;
    private ?float $colFloat = null;
    private ?int $colInteger = null;
    private ?int $colSmallint = null;
    private ?string $colText = null;
    private ?\PSX\DateTime\LocalTime $colTime = null;
    private ?string $colString = null;
    private mixed $colJson = null;
    private ?string $colGuid = null;
    public function setId(int $id): void
    {
        $this->id = $id;
    }
    public function getId(): int
    {
        return $this->id ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "id" was provided');
    }
    public function setColBigint(string $colBigint): void
    {
        $this->colBigint = $colBigint;
    }
    public function getColBigint(): string
    {
        return $this->colBigint ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "col_bigint" was provided');
    }
    public function setColBinary(mixed $colBinary): void
    {
        $this->colBinary = $colBinary;
    }
    public function getColBinary(): mixed
    {
        return $this->colBinary ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "col_binary" was provided');
    }
    public function setColBlob(mixed $colBlob): void
    {
        $this->colBlob = $colBlob;
    }
    public function getColBlob(): mixed
    {
        return $this->colBlob ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "col_blob" was provided');
    }
    public function setColBoolean(bool $colBoolean): void
    {
        $this->colBoolean = $colBoolean;
    }
    public function getColBoolean(): bool
    {
        return $this->colBoolean ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "col_boolean" was provided');
    }
    public function setColDatetime(\PSX\DateTime\LocalDateTime $colDatetime): void
    {
        $this->colDatetime = $colDatetime;
    }
    public function getColDatetime(): \PSX\DateTime\LocalDateTime
    {
        return $this->colDatetime ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "col_datetime" was provided');
    }
    public function setColDatetimetz(\PSX\DateTime\LocalDateTime $colDatetimetz): void
    {
        $this->colDatetimetz = $colDatetimetz;
    }
    public function getColDatetimetz(): \PSX\DateTime\LocalDateTime
    {
        return $this->colDatetimetz ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "col_datetimetz" was provided');
    }
    public function setColDate(\PSX\DateTime\LocalDate $colDate): void
    {
        $this->colDate = $colDate;
    }
    public function getColDate(): \PSX\DateTime\LocalDate
    {
        return $this->colDate ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "col_date" was provided');
    }
    public function setColDecimal(string $colDecimal): void
    {
        $this->colDecimal = $colDecimal;
    }
    public function getColDecimal(): string
    {
        return $this->colDecimal ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "col_decimal" was provided');
    }
    public function setColFloat(float $colFloat): void
    {
        $this->colFloat = $colFloat;
    }
    public function getColFloat(): float
    {
        return $this->colFloat ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "col_float" was provided');
    }
    public function setColInteger(int $colInteger): void
    {
        $this->colInteger = $colInteger;
    }
    public function getColInteger(): int
    {
        return $this->colInteger ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "col_integer" was provided');
    }
    public function setColSmallint(int $colSmallint): void
    {
        $this->colSmallint = $colSmallint;
    }
    public function getColSmallint(): int
    {
        return $this->colSmallint ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "col_smallint" was provided');
    }
    public function setColText(string $colText): void
    {
        $this->colText = $colText;
    }
    public function getColText(): string
    {
        return $this->colText ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "col_text" was provided');
    }
    public function setColTime(\PSX\DateTime\LocalTime $colTime): void
    {
        $this->colTime = $colTime;
    }
    public function getColTime(): \PSX\DateTime\LocalTime
    {
        return $this->colTime ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "col_time" was provided');
    }
    public function setColString(string $colString): void
    {
        $this->colString = $colString;
    }
    public function getColString(): string
    {
        return $this->colString ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "col_string" was provided');
    }
    public function setColJson(mixed $colJson): void
    {
        $this->colJson = $colJson;
    }
    public function getColJson(): mixed
    {
        return $this->colJson ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "col_json" was provided');
    }
    public function setColGuid(string $colGuid): void
    {
        $this->colGuid = $colGuid;
    }
    public function getColGuid(): string
    {
        return $this->colGuid ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "col_guid" was provided');
    }
    public function toRecord(): \PSX\Record\RecordInterface
    {
        /** @var \PSX\Record\Record<mixed> $record */
        $record = new \PSX\Record\Record();
        $record->put('id', $this->id);
        $record->put('col_bigint', $this->colBigint);
        $record->put('col_binary', $this->colBinary);
        $record->put('col_blob', $this->colBlob);
        $record->put('col_boolean', $this->colBoolean);
        $record->put('col_datetime', $this->colDatetime);
        $record->put('col_datetimetz', $this->colDatetimetz);
        $record->put('col_date', $this->colDate);
        $record->put('col_decimal', $this->colDecimal);
        $record->put('col_float', $this->colFloat);
        $record->put('col_integer', $this->colInteger);
        $record->put('col_smallint', $this->colSmallint);
        $record->put('col_text', $this->colText);
        $record->put('col_time', $this->colTime);
        $record->put('col_string', $this->colString);
        $record->put('col_json', $this->colJson);
        $record->put('col_guid', $this->colGuid);
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
        $row->colBigint = isset($data['col_bigint']) && is_string($data['col_bigint']) ? $data['col_bigint'] : null;
        $row->colBinary = isset($data['col_binary']) ? $data['col_binary'] : null;
        $row->colBlob = isset($data['col_blob']) ? $data['col_blob'] : null;
        $row->colBoolean = isset($data['col_boolean']) && is_bool($data['col_boolean']) ? $data['col_boolean'] : null;
        $row->colDatetime = isset($data['col_datetime']) && $data['col_datetime'] instanceof \DateTimeInterface ? \PSX\DateTime\LocalDateTime::from($data['col_datetime']) : null;
        $row->colDatetimetz = isset($data['col_datetimetz']) && $data['col_datetimetz'] instanceof \DateTimeInterface ? \PSX\DateTime\LocalDateTime::from($data['col_datetimetz']) : null;
        $row->colDate = isset($data['col_date']) && $data['col_date'] instanceof \DateTimeInterface ? \PSX\DateTime\LocalDate::from($data['col_date']) : null;
        $row->colDecimal = isset($data['col_decimal']) && is_string($data['col_decimal']) ? $data['col_decimal'] : null;
        $row->colFloat = isset($data['col_float']) && is_float($data['col_float']) ? $data['col_float'] : null;
        $row->colInteger = isset($data['col_integer']) && is_int($data['col_integer']) ? $data['col_integer'] : null;
        $row->colSmallint = isset($data['col_smallint']) && is_int($data['col_smallint']) ? $data['col_smallint'] : null;
        $row->colText = isset($data['col_text']) && is_string($data['col_text']) ? $data['col_text'] : null;
        $row->colTime = isset($data['col_time']) && $data['col_time'] instanceof \DateTimeInterface ? \PSX\DateTime\LocalTime::from($data['col_time']) : null;
        $row->colString = isset($data['col_string']) && is_string($data['col_string']) ? $data['col_string'] : null;
        $row->colJson = isset($data['col_json']) ? $data['col_json'] : null;
        $row->colGuid = isset($data['col_guid']) && is_string($data['col_guid']) ? $data['col_guid'] : null;
        return $row;
    }
}