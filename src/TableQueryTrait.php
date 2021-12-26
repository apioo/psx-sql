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

use Doctrine\DBAL\Query\QueryBuilder;
use PSX\Record\Record;
use PSX\Record\RecordInterface;

/**
 * TableQueryTrait
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://phpsx.org
 */
trait TableQueryTrait
{
    public function getAll(?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null, ?Condition $condition = null, ?Fields $fields = null): iterable
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
            $sortBy = $this->getPrimaryKey();
        }

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
    }

    public function getBy(Condition $condition, ?Fields $fields = null, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null): iterable
    {
        return $this->getAll($startIndex, $count, $sortBy, $sortOrder, $condition, $fields);
    }

    public function getOneBy(Condition $condition, ?Fields $fields = null): ?RecordInterface
    {
        $result = $this->getAll(0, 1, null, null, $condition, $fields);
        foreach ($result as $row) {
            return $row;
        }

        return null;
    }

    public function get(string|int $id, ?Fields $fields = null): ?RecordInterface
    {
        $condition = new Condition(array($this->getPrimaryKey(), '=', $id));

        return $this->getOneBy($condition, $fields);
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function getCount(?Condition $condition = null): int
    {
        [$sql, $parameters] = $this->getQueryCount(
            $this->getName(),
            $condition
        );

        return (int) $this->connection->fetchOne($sql, $parameters);
    }

    public function getSupportedFields(): array
    {
        return array_keys($this->getColumns());
    }

    public function newRecord(): RecordInterface
    {
        $supported = $this->getSupportedFields();
        $fields    = array_combine($supported, array_fill(0, count($supported), null));

        return new Record($fields);
    }

    /**
     * Returns an array which contains as first value a SQL query and as second
     * an array of parameters. Uses by default the dbal query builder to create
     * the SQL query. The query is used for the default query methods
     */
    protected function getQuery(string $table, array $fields, int $startIndex, int $count, string $sortBy, int $sortOrder, ?Condition $condition = null): array
    {
        $builder = $this->newQueryBuilder($table)
            ->select($fields)
            ->orderBy($sortBy, $sortOrder == Sql::SORT_ASC ? 'ASC' : 'DESC')
            ->setFirstResult($startIndex)
            ->setMaxResults($count);

        return $this->convertBuilder($builder, $condition);
    }

    /**
     * Returns an array which contains as first value a SQL query and as second
     * an array of parameters. Uses by default the dbal query builder to create
     * the SQL query. The query is used for the count method
     *
     * @throws \Doctrine\DBAL\Exception
     */
    protected function getQueryCount(string $table, ?Condition $condition = null): array
    {
        $builder = $this->newQueryBuilder($table)
            ->select($this->connection->getDatabasePlatform()->getCountExpression($this->getPrimaryKey()));

        return $this->convertBuilder($builder, $condition);
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    protected function project(string $sql, array $params = array(), array $columns = null): array
    {
        $result  = array();
        $columns = $columns === null ? $this->getColumns() : $columns;
        $stmt    = $this->connection->executeQuery($sql, $params ?: array());
        $class   = $this->getRecordClass();

        while ($row = $stmt->fetchAssociative()) {
            foreach ($row as $key => $value) {
                if (isset($columns[$key])) {
                    $value = $this->unserializeType($value, $columns[$key]);
                }

                $row[$key] = $value;
            }

            $result[] = new $class($row);
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
        return $this->getPrimaryKey();
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
     * Returns the default record class
     */
    protected function getRecordClass(): string
    {
        return Record::class;
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    private function convertBuilder(QueryBuilder $builder, ?Condition $condition = null): array
    {
        if ($condition !== null && $condition->hasCondition()) {
            $builder->where($condition->getExpression($this->connection->getDatabasePlatform()));

            $values = $condition->getValues();
            foreach ($values as $key => $value) {
                $builder->setParameter($key, $value);
            }
        }

        return [$builder->getSQL(), $builder->getParameters()];
    }
}
