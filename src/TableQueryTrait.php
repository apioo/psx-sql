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

use Doctrine\DBAL\Exception as DBALException;
use Doctrine\DBAL\Driver\Exception as DBALDriverException;
use Doctrine\DBAL\Query\QueryBuilder;
use PSX\Sql\Exception\QueryException;

/**
 * TableQueryTrait
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://phpsx.org
 */
trait TableQueryTrait
{
    /**
     * @throws QueryException
     * @internal
     */
    protected function doFindAll(?Condition $condition = null, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null, ?Fields $fields = null): iterable
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
     * @internal
     */
    protected function doFindBy(Condition $condition, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null, ?Fields $fields = null): iterable
    {
        return $this->doFindAll($condition, $startIndex, $count, $sortBy, $sortOrder, $fields);
    }

    /**
     * @throws QueryException
     * @internal
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
    protected function project(string $sql, array $params = array(), array $columns = null): array
    {
        $result  = array();
        $columns = $columns === null ? $this->getColumns() : $columns;
        $stmt    = $this->connection->executeQuery($sql, $params ?: array());

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

    abstract protected function newRecord(array $row): object;

    /**
     * @throws DBALException
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
