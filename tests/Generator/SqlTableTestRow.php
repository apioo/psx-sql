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