<?php
/*
 * PSX is a open source PHP framework to develop RESTful APIs.
 * For the current version and informations visit <http://phpsx.org>
 *
 * Copyright 2010-2016 Christoph Kappestein <k42b3.x@gmail.com>
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

use InvalidArgumentException;
use Doctrine\DBAL\Connection;

/**
 * TableAbstract
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    http://phpsx.org
 */
abstract class TableAbstract implements TableInterface
{
    use TableQueryTrait;
    use TableManipulationTrait;

    /**
     * @var \Doctrine\DBAL\Connection
     */
    protected $connection;

    /**
     * @var \PSX\Sql\TableManagerInterface
     */
    private $tableManager;

    /**
     * @var \PSX\Sql\Builder
     */
    private $builder;

    public function __construct(TableManager $tableManager)
    {
        $this->connection   = $tableManager->getConnection();
        $this->tableManager = $tableManager;
        $this->builder      = new Builder();
    }

    public function getDisplayName()
    {
        $name = $this->getName();
        $pos  = strrpos($name, '_');

        return $pos !== false ? substr($name, $pos + 1) : $name;
    }

    public function getPrimaryKey()
    {
        return $this->getFirstColumnWithAttr(self::PRIMARY_KEY);
    }

    public function hasColumn($column)
    {
        $columns = $this->getColumns();

        return isset($columns[$column]);
    }

    protected function getFirstColumnWithAttr($searchAttr)
    {
        $columns = $this->getColumns();

        foreach ($columns as $column => $attr) {
            if ($attr & $searchAttr) {
                return $column;
            }
        }

        return null;
    }

    protected function unserializeType($value, $type)
    {
        return $this->connection->convertToPHPValue(
            $value,
            TypeMapper::getDoctrineTypeByType($type)
        );
    }

    protected function serializeType($value, $type)
    {
        return $this->connection->convertToDatabaseValue(
            $value,
            TypeMapper::getDoctrineTypeByType($type)
        );
    }

    protected function getTable($tableName)
    {
        return $this->tableManager->getTable($tableName);
    }

    protected function build($definition)
    {
        return $this->builder->build($definition);
    }

    protected function doCollection($source, array $arguments, array $definition, $key = null)
    {
        if (is_callable($source)) {
            return new Provider\Callback\Collection($source, $arguments, $definition, $key);
        } elseif (is_string($source)) {
            return new Provider\DBAL\Collection($this->connection, $source, $arguments, $definition, $key);
        } elseif (is_array($source)) {
            return new Provider\Map\Collection($source, $definition, $key);
        } else {
            throw new InvalidArgumentException('Source must be either a callable, string or array');
        }
    }

    protected function doEntity($source, array $arguments, array $definition)
    {
        if (is_callable($source)) {
            return new Provider\Callback\Entity($source, $arguments, $definition);
        } elseif (is_string($source)) {
            return new Provider\DBAL\Entity($this->connection, $source, $arguments, $definition);
        } elseif (is_array($source)) {
            return new Provider\Map\Entity($source, $definition);
        } else {
            throw new InvalidArgumentException('Source must be either a callable, string or array');
        }
    }

    protected function dateTime($value)
    {
        return new Field\DateTime($value);
    }

    protected function callback($key, \Closure $callback)
    {
        return new Field\Callback($key, $callback);
    }

    protected function replace($value)
    {
        return new Field\Replace($value);
    }

    protected function type($key, $type)
    {
        return new Field\Type($key, $this->connection, $type);
    }

    protected function value($value)
    {
        return new Field\Value($value);
    }
}
