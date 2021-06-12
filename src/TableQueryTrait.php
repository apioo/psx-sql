<?php
/*
 * PSX is a open source PHP framework to develop RESTful APIs.
 * For the current version and informations visit <http://phpsx.org>
 *
 * Copyright 2010-2017 Christoph Kappestein <christoph.kappestein@gmail.com>
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

use BadMethodCallException;
use Doctrine\DBAL\Query\QueryBuilder;
use InvalidArgumentException;
use PSX\Record\Record;

/**
 * TableQueryTrait
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    http://phpsx.org
 */
trait TableQueryTrait
{
    /**
     * @param integer $startIndex
     * @param integer $count
     * @param string $sortBy
     * @param integer $sortOrder
     * @param Condition|null $condition
     * @param Fields|null $fields
     * @param \Closure|null $hydrator
     * @return \PSX\Record\Record[]
     */
    public function getAll($startIndex = null, $count = null, $sortBy = null, $sortOrder = null, Condition $condition = null, Fields $fields = null, ?\Closure $hydrator = null)
    {
        $startIndex = $startIndex !== null ? (int) $startIndex : 0;
        $count      = !empty($count)       ? (int) $count      : $this->limit();
        $sortBy     = $sortBy     !== null ? $sortBy           : $this->sortKey();
        $sortOrder  = $sortOrder  !== null ? (int) $sortOrder  : $this->sortOrder();

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

        return $this->project($sql, $parameters, null, $hydrator);
    }

    /**
     * @param Condition $condition
     * @param Fields|null $fields
     * @param integer $startIndex
     * @param integer $count
     * @param string $sortBy
     * @param integer $sortOrder
     * @return \PSX\Record\Record[]
     */
    public function getBy(Condition $condition, Fields $fields = null, $startIndex = null, $count = null, $sortBy = null, $sortOrder = null)
    {
        return $this->getAll($startIndex, $count, $sortBy, $sortOrder, $condition, $fields);
    }

    /**
     * @param Condition $condition
     * @param Fields|null $fields
     * @return \PSX\Record\Record
     */
    public function getOneBy(Condition $condition, Fields $fields = null)
    {
        $result = $this->getAll(0, 1, null, null, $condition, $fields);

        return current($result);
    }

    /**
     * @param integer $id
     * @param Fields|null $fields
     * @return \PSX\Record\Record
     */
    public function get($id, Fields $fields = null)
    {
        $condition = new Condition(array($this->getPrimaryKey(), '=', $id));

        return $this->getOneBy($condition, $fields);
    }

    /**
     * @param Condition|null $condition
     * @return integer
     */
    public function getCount(Condition $condition = null)
    {
        [$sql, $parameters] = $this->getQueryCount(
            $this->getName(),
            $condition
        );

        return (int) $this->connection->fetchColumn($sql, $parameters);
    }

    /**
     * @return array
     */
    public function getSupportedFields()
    {
        return array_keys($this->getColumns());
    }

    /**
     * @return \PSX\Record\Record
     * @deprecated
     */
    public function getRecord()
    {
        $supported = $this->getSupportedFields();
        $fields    = array_combine($supported, array_fill(0, count($supported), null));

        return new Record('record', $fields);
    }

    /**
     * Magic method to make conditional selection
     *
     * @param string $method
     * @param string $arguments
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        if (substr($method, 0, 8) == 'getOneBy') {
            $column = lcfirst(substr($method, 8));
            $value  = isset($arguments[0]) ? $arguments[0] : null;
            $fields = isset($arguments[1]) ? $arguments[1] : null;

            if (!empty($value)) {
                $condition = new Condition(array($column, '=', $value));
            } else {
                throw new InvalidArgumentException('Value required');
            }

            if ($fields !== null && !$fields instanceof Fields) {
                throw new InvalidArgumentException('Invalid fields provided must be an instance of Fields');
            }

            return $this->getOneBy($condition, $fields);
        } elseif (substr($method, 0, 5) == 'getBy') {
            $column = lcfirst(substr($method, 5));
            $value  = isset($arguments[0]) ? $arguments[0] : null;
            $fields = isset($arguments[1]) ? $arguments[1] : null;

            if (!empty($value)) {
                $condition = new Condition(array($column, '=', $value));
            } else {
                throw new InvalidArgumentException('Value required');
            }

            if ($fields !== null && !$fields instanceof Fields) {
                throw new InvalidArgumentException('Invalid fields provided must be an instance of Fields');
            }

            $startIndex = isset($arguments[2]) ? $arguments[2] : null;
            $count      = isset($arguments[3]) ? $arguments[3] : null;
            $sortBy     = isset($arguments[4]) ? $arguments[4] : null;
            $sortOrder  = isset($arguments[5]) ? $arguments[5] : null;

            return $this->getBy($condition, $fields, $startIndex, $count, $sortBy, $sortOrder);
        } else {
            throw new BadMethodCallException('Undefined method ' . $method);
        }
    }

    /**
     * Returns an array which contains as first value a SQL query and as second
     * an array of parameters. Uses by default the dbal query builder to create
     * the SQL query. The query is used for the default query methods
     *
     * @param string $table
     * @param array $fields
     * @param integer $startIndex
     * @param integer $count
     * @param string $sortBy
     * @param string $sortOrder
     * @param \PSX\Sql\Condition|null $condition
     * @return array
     */
    protected function getQuery($table, array $fields, $startIndex, $count, $sortBy, $sortOrder, Condition $condition = null)
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
     * @param string $table
     * @param \PSX\Sql\Condition|null $condition
     * @return array
     */
    protected function getQueryCount($table, Condition $condition = null)
    {
        $builder = $this->newQueryBuilder($table)
            ->select($this->connection->getDatabasePlatform()->getCountExpression($this->getPrimaryKey()));

        return $this->convertBuilder($builder, $condition);
    }

    protected function project($sql, array $params = array(), array $columns = null, ?\Closure $hydrator = null)
    {
        $result  = array();
        $columns = $columns === null ? $this->getColumns() : $columns;
        $stmt    = $this->connection->executeQuery($sql, $params ?: array());
        $name    = $this->getDisplayName();

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            foreach ($row as $key => $value) {
                if (isset($columns[$key])) {
                    $value = $this->unserializeType($value, $columns[$key]);
                }

                $row[$key] = $value;
            }

            if ($hydrator instanceof \Closure) {
                $result[] = $hydrator($row);
            } else {
                $result[] = new Record($name, $row);
            }
        }

        $stmt->closeCursor();

        return $result;
    }

    protected function limit()
    {
        return 16;
    }

    protected function sortKey()
    {
        return $this->getPrimaryKey();
    }

    protected function sortOrder()
    {
        return Sql::SORT_DESC;
    }

    /**
     * @param string $table
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    protected function newQueryBuilder($table)
    {
        return $this->connection->createQueryBuilder()
            ->from($table, null);
    }

    /**
     * @param \Doctrine\DBAL\Query\QueryBuilder $builder
     * @param \PSX\Sql\Condition|null $condition
     * @return array
     */
    private function convertBuilder(QueryBuilder $builder, Condition $condition = null)
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
