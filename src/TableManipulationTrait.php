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

use InvalidArgumentException;
use PSX\Record\RecordInterface;
use RuntimeException;

/**
 * TableManipulationTrait
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    http://phpsx.org
 */
trait TableManipulationTrait
{
    protected $lastInsertId;

    /**
     * @param array|\stdClass|\PSX\Record\RecordInterface $record
     * @return integer
     */
    public function create($record)
    {
        $fields = $this->serializeFields($this->getArray($record));

        if (!empty($fields)) {
            $result = $this->connection->insert($this->getName(), $fields);

            // set last insert id
            $this->lastInsertId = $this->connection->lastInsertId();

            return $result;
        } else {
            throw new RuntimeException('No valid field set');
        }
    }

    /**
     * @param array|\stdClass|\PSX\Record\RecordInterface $record
     * @return integer
     */
    public function update($record)
    {
        $fields = $this->serializeFields($this->getArray($record));

        if (!empty($fields)) {
            $pk = $this->getPrimaryKey();

            if (isset($fields[$pk])) {
                $condition = [$pk => $fields[$pk]];
            } else {
                throw new RuntimeException('No primary key set');
            }

            return $this->connection->update($this->getName(), $fields, $condition);
        } else {
            throw new RuntimeException('No valid field set');
        }
    }

    /**
     * @param array|\stdClass|\PSX\Record\RecordInterface $record
     * @return integer
     */
    public function delete($record)
    {
        $fields = $this->serializeFields($this->getArray($record));

        if (!empty($fields)) {
            $pk = $this->getPrimaryKey();

            if (isset($fields[$pk])) {
                $condition = [$pk => $fields[$pk]];
            } else {
                throw new RuntimeException('No primary key set');
            }

            return $this->connection->delete($this->getName(), $condition);
        } else {
            throw new RuntimeException('No valid field set');
        }
    }

    /**
     * @return integer
     */
    public function getLastInsertId()
    {
        return $this->lastInsertId;
    }

    /**
     * Returns an array which can be used by the dbal insert, update and delete
     * methods
     *
     * @param array $row
     * @return array
     */
    protected function serializeFields(array $row)
    {
        $data    = array();
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

    protected function getArray($record)
    {
        if ($record instanceof RecordInterface) {
            return $record->getProperties();
        } elseif ($record instanceof \stdClass) {
            return (array) $record;
        } elseif ($record instanceof \ArrayObject) {
            return $record->getArrayCopy();
        } elseif (is_array($record)) {
            return $record;
        } else {
            throw new InvalidArgumentException('Record must bei either an PSX\Record\RecordInterface, stdClass or array');
        }
    }
}
