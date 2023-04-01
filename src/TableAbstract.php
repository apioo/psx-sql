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
use Doctrine\DBAL\Driver\Exception as DBALDriverException;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\DBAL\Query\QueryBuilder;
use PSX\DateTime\Duration;
use PSX\DateTime\LocalDate;
use PSX\DateTime\LocalDateTime;
use PSX\DateTime\LocalTime;
use PSX\DateTime\Period;
use PSX\Record\RecordInterface;
use PSX\Sql\Exception\ManipulationException;
use PSX\Sql\Exception\NoFieldsAvailableException;
use PSX\Sql\Exception\NoLastInsertIdAvailable;
use PSX\Sql\Exception\NoPrimaryKeyAvailableException;
use PSX\Sql\Exception\QueryException;

/**
 * TableAbstract
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://phpsx.org
 * @template T
 */
abstract class TableAbstract implements TableInterface
{
    use ViewTrait;

    protected Connection $connection;
    protected ?int $lastInsertId = null;

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

    public function getColumnNames(): array
    {
        return array_keys($this->getColumns());
    }

    /**
     * @throws QueryException
     */
    public function getCount(?Condition $condition = null): int
    {
        try {
            [$sql, $parameters] = $this->getQueryCount(
                $this->getName(),
                $condition
            );

            return (int) $this->connection->fetchOne($sql, $parameters);
        } catch (DBALException $e) {
            throw new QueryException($e->getMessage(), 0, $e);
        }
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

    public function getLastInsertId(): int
    {
        if ($this->lastInsertId === null) {
            throw new NoLastInsertIdAvailable('No last insert id available');
        }

        return $this->lastInsertId;
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
        if ($value instanceof LocalDate) {
            $value = $value->toDateTime();
        } elseif ($value instanceof LocalDateTime) {
            $value = $value->toDateTime();
        } elseif ($value instanceof LocalTime) {
            $value = $value->toDateTime();
        }

        return $this->connection->convertToDatabaseValue(
            $value,
            TypeMapper::getDoctrineTypeByType($type)
        );
    }

    /**
     * @throws QueryException
     * @return array<T>
     */
    protected function doFindAll(?Condition $condition = null, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null, ?Fields $fields = null): array
    {
        $startIndex = $startIndex !== null ? $startIndex : 0;
        $count = !empty($count) ? $count : $this->limit();
        $sortBy = $sortBy !== null ? $sortBy : $this->sortKey();
        $sortOrder = $sortOrder !== null ? $sortOrder : $this->sortOrder();

        if ($fields !== null) {
            $fieldsWhitelist = $fields->getWhitelist();
            $fieldsBlacklist = $fields->getBlacklist();
        } else {
            $fieldsWhitelist = null;
            $fieldsBlacklist = null;
        }

        $columns = array_keys($this->getColumns());

        if (!empty($fieldsWhitelist)) {
            $columns = array_intersect($columns, $fieldsWhitelist);
        } elseif (!empty($fieldsBlacklist)) {
            $columns = array_diff($columns, $fieldsBlacklist);
        }

        if (!in_array($sortBy, $columns)) {
            $sortBy = $this->getPrimaryKeys()[0] ?? null;
        }

        try {
            [$sql, $parameters] = $this->getQuery(
                $this->getName(),
                $columns,
                $startIndex,
                $count,
                $sortBy,
                $sortOrder,
                $condition
            );

            return $this->project($sql, $parameters);
        } catch (DBALException $e) {
            throw new QueryException($e->getMessage(), 0, $e);
        } catch (DBALDriverException $e) {
            throw new QueryException($e->getMessage(), 0, $e);
        }
    }

    /**
     * @throws QueryException
     * @return array<T>
     */
    protected function doFindBy(Condition $condition, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null, ?Fields $fields = null): array
    {
        return $this->doFindAll($condition, $startIndex, $count, $sortBy, $sortOrder, $fields);
    }

    /**
     * @throws QueryException
     * @return T
     */
    protected function doFindOneBy(Condition $condition, ?Fields $fields = null): mixed
    {
        $result = $this->doFindAll($condition, 0, 1, null, null, $fields);
        foreach ($result as $row) {
            return $row;
        }

        return null;
    }

    /**
     * Returns an array which contains as first value a SQL query and as second an array of parameters. Uses by default
     * the dbal query builder to create the SQL query. The query is used for the default query methods
     *
     * @throws DBALException
     */
    protected function getQuery(string $table, array $fields, int $startIndex, int $count, ?string $sortBy, int $sortOrder, ?Condition $condition = null): array
    {
        $builder = $this->newQueryBuilder($table)
            ->select($fields)
            ->setFirstResult($startIndex)
            ->setMaxResults($count);

        if ($sortBy !== null) {
            $builder->orderBy($sortBy, $sortOrder == Sql::SORT_ASC ? 'ASC' : 'DESC');
        }

        return $this->convertBuilder($builder, $condition);
    }

    /**
     * Returns an array which contains as first value a SQL query and as second an array of parameters. Uses by default
     * the dbal query builder to create the SQL query. The query is used for the count method
     *
     * @throws QueryException
     */
    protected function getQueryCount(string $table, ?Condition $condition = null): array
    {
        try {
            $builder = $this->newQueryBuilder($table)
                ->select($this->connection->getDatabasePlatform()->getCountExpression('*'));

            return $this->convertBuilder($builder, $condition);
        } catch (DBALException $e) {
            throw new QueryException($e->getMessage(), 0, $e);
        }
    }

    /**
     * @throws DBALException
     * @throws DBALDriverException
     */
    protected function project(string $sql, array $params = [], array $columns = null): array
    {
        $result  = [];
        $columns = $columns === null ? $this->getColumns() : $columns;
        $stmt    = $this->connection->executeQuery($sql, $params ?: []);

        while ($row = $stmt->fetchAssociative()) {
            foreach ($row as $key => $value) {
                if (isset($columns[$key])) {
                    $value = $this->unserializeType($value, $columns[$key]);
                }

                $row[$key] = $value;
            }

            $result[] = $this->newRecord($row);
        }

        $stmt->free();

        return $result;
    }

    protected function limit(): int
    {
        return 16;
    }

    protected function sortKey(): ?string
    {
        return $this->getPrimaryKeys()[0] ?? null;
    }

    protected function sortOrder(): int
    {
        return Sql::SORT_DESC;
    }

    protected function newQueryBuilder(string $table): QueryBuilder
    {
        return $this->connection->createQueryBuilder()
            ->from($table, null);
    }

    /**
     * @param array<string, mixed> $row
     */
    abstract protected function newRecord(array $row): object;

    /**
     * @throws DBALException
     */
    private function convertBuilder(QueryBuilder $builder, ?Condition $condition = null, int $baseIndex = 0): array
    {
        if ($condition !== null && $condition->hasCondition()) {
            $builder->where($condition->getExpression($this->connection->getDatabasePlatform()));

            $values = $condition->getValues();
            foreach ($values as $key => $value) {
                $builder->setParameter($baseIndex + $key, $value);
            }
        }

        return [$builder->getSQL(), $builder->getParameters()];
    }

    /**
     * @throws ManipulationException
     */
    protected function doCreate(RecordInterface $record): int
    {
        try {
            $fields = $this->getFields($record);
            $result = $this->connection->insert($this->getName(), $fields);

            $this->lastInsertId = (int) $this->connection->lastInsertId();

            return (int) $result;
        } catch (DBALException $e) {
            throw new ManipulationException($e->getMessage(), 0, $e);
        }
    }

    /**
     * @throws ManipulationException
     */
    protected function doUpdate(RecordInterface $record): int
    {
        try {
            $fields = $this->getFields($record);
            $criteria = $this->getCriteria($fields);

            return (int) $this->connection->update($this->getName(), $fields, $criteria);
        } catch (DBALException $e) {
            throw new ManipulationException($e->getMessage(), 0, $e);
        }
    }

    /**
     * @throws ManipulationException
     */
    protected function doUpdateBy(Condition $condition, RecordInterface $record): int
    {
        try {
            $queryBuilder = $this->connection->createQueryBuilder();
            $queryBuilder->update($this->getName());

            $index = 0;
            foreach ($record->getAll() as $column => $value) {
                $queryBuilder->set($column, '?');
                $queryBuilder->setParameter($index, $value);
                $index++;
            }

            [$sql, $parameters] = $this->convertBuilder($queryBuilder, $condition, $index);

            return (int) $this->connection->executeStatement($sql, $parameters);
        } catch (DBALException $e) {
            throw new ManipulationException($e->getMessage(), 0, $e);
        }
    }

    /**
     * @throws ManipulationException
     */
    protected function doDelete(RecordInterface $record): int
    {
        try {
            $fields = $this->getFields($record);
            $criteria = $this->getCriteria($fields);

            return (int) $this->connection->delete($this->getName(), $criteria);
        } catch (DBALException $e) {
            throw new ManipulationException($e->getMessage(), 0, $e);
        }
    }

    /**
     * @throws ManipulationException
     */
    protected function doDeleteBy(Condition $condition): int
    {
        try {
            $queryBuilder = $this->connection->createQueryBuilder();
            $queryBuilder->delete($this->getName());

            [$sql, $parameters] = $this->convertBuilder($queryBuilder, $condition);

            return (int) $this->connection->executeStatement($sql, $parameters);
        } catch (DBALException $e) {
            throw new ManipulationException($e->getMessage(), 0, $e);
        }
    }

    /**
     * @throws NoPrimaryKeyAvailableException
     */
    private function getCriteria(array $fields): array
    {
        $primaryKeys = $this->getPrimaryKeys();
        $criteria = [];
        foreach ($primaryKeys as $primaryKey) {
            if (!isset($fields[$primaryKey])) {
                throw new NoPrimaryKeyAvailableException('Primary key field not set on record');
            }

            $criteria[$primaryKey] = $fields[$primaryKey];
        }

        if (empty($criteria)) {
            throw new NoPrimaryKeyAvailableException('No primary key available on table');
        }

        return $criteria;
    }

    /**
     * @throws NoFieldsAvailableException
     */
    private function getFields(RecordInterface $record): array
    {
        $fields = $this->serializeFields($record->getAll());
        if (empty($fields)) {
            throw new NoFieldsAvailableException('No valid field set');
        }

        return $fields;
    }

    /**
     * Returns an array which can be used by the dbal insert, update and delete methods
     */
    protected function serializeFields(array $row): array
    {
        $data    = [];
        $columns = $this->getColumns();

        $builder = $this->newQueryBuilder($this->getName());
        $parts   = $builder->getQueryPart('from');
        $part    = reset($parts);
        $alias   = $part['alias'];

        foreach ($columns as $name => $type) {
            if (!empty($alias)) {
                $pos = strpos($name, $alias . '.');
                if ($pos !== false) {
                    $name = substr($name, strlen($alias) + 1);
                }
            }

            if (isset($row[$name])) {
                $data[$name] = $this->serializeType($row[$name], $type);
            }
        }

        return $data;
    }
}
