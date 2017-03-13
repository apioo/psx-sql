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

use Doctrine\DBAL\Connection;
use InvalidArgumentException;

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
    use ViewTrait;

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

    public function beginTransaction()
    {
        $this->connection->beginTransaction();
    }

    public function commit()
    {
        $this->connection->commit();
    }

    public function rollBack()
    {
        $this->connection->rollBack();
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

    /**
     * @param $value
     * @return Field\DateTime
     * @deprecated
     */
    protected function dateTime($value)
    {
        return $this->fieldDateTime($value);
    }

    /**
     * @param $key
     * @param \Closure $callback
     * @return Field\Callback
     * @deprecated 
     */
    protected function callback($key, \Closure $callback)
    {
        return $this->fieldCallback($key, $callback);
    }

    /**
     * @param $value
     * @return Field\Replace
     * @deprecated 
     */
    protected function replace($value)
    {
        return $this->fieldReplace($value);
    }

    /**
     * @param $key
     * @param $type
     * @return Field\Type
     * @deprecated 
     */
    protected function type($key, $type)
    {
        return $this->fieldType($key, $type);
    }

    /**
     * @param $value
     * @return Field\Value
     * @deprecated 
     */
    protected function value($value)
    {
        return $this->fieldValue($value);
    }
}
