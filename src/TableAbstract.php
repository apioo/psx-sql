<?php
/*
 * PSX is an open source PHP framework to develop RESTful APIs.
 * For the current version and information visit <https://phpsx.org>
 *
 * Copyright 2010-2022 Christoph Kappestein <christoph.kappestein@gmail.com>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace PSX\Sql;

use Doctrine\DBAL\Connection;

/**
 * TableAbstract
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://phpsx.org
 *
 * @template T
 * @implements TableInterface<T>
 */
abstract class TableAbstract implements TableInterface
{
    /**
     * @use TableQueryTrait<T>
     */
    use TableQueryTrait;

    /**
     * @use TableManipulationTrait<T>
     */
    use TableManipulationTrait;

    use ViewTrait;

    protected Connection $connection;

    private TableManagerInterface $tableManager;
    private Builder $builder;

    public function __construct(TableManager $tableManager)
    {
        $this->connection   = $tableManager->getConnection();
        $this->tableManager = $tableManager;
        $this->builder      = new Builder($this->connection);
    }

    public function getDisplayName(): string
    {
        $name = $this->getName();
        $pos  = strrpos($name, '_');

        return $pos !== false ? substr($name, $pos + 1) : $name;
    }

    public function getPrimaryKeys(): array
    {
        return $this->getColumnsWithAttribute(self::PRIMARY_KEY);
    }

    public function hasColumn(string $column): bool
    {
        $columns = $this->getColumns();

        return isset($columns[$column]);
    }

    public function beginTransaction(): void
    {
        $this->connection->beginTransaction();
    }

    public function commit(): void
    {
        $this->connection->commit();
    }

    public function rollBack(): void
    {
        $this->connection->rollBack();
    }

    protected function getColumnsWithAttribute(int $searchAttribute): array
    {
        $result = [];
        foreach ($this->getColumns() as $column => $attribute) {
            if ($attribute & $searchAttribute) {
                $result[] = $column;
            }
        }

        return $result;
    }

    /**
     * Returns a php type based on the serialized string representation
     */
    protected function unserializeType(mixed $value, int $type): mixed
    {
        if ($type === self::TYPE_JSON) {
            // overwrite the doctrine json type since it uses the assoc parameter. This is a problem for empty objects
            // since we cant distinguish between an empty array or object
            if ($value === null) {
                return null;
            } elseif ($value === '') {
                return new \stdClass();
            } elseif (is_resource($value)) {
                $value = stream_get_contents($value);
            }

            return json_decode($value);
        }

        return $this->connection->convertToPHPValue(
            $value,
            TypeMapper::getDoctrineTypeByType($type)
        );
    }

    /**
     * Returns a string representation which can be stored in the database
     */
    protected function serializeType(mixed $value, int $type): string
    {
        return $this->connection->convertToDatabaseValue(
            $value,
            TypeMapper::getDoctrineTypeByType($type)
        );
    }
}
