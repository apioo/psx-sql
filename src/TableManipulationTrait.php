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
use PSX\Record\RecordInterface;
use PSX\Sql\Exception\ManipulationException;
use PSX\Sql\Exception\NoFieldsAvailableException;
use PSX\Sql\Exception\NoPrimaryKeyAvailableException;

/**
 * TableManipulationTrait
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://phpsx.org
 */
trait TableManipulationTrait
{
    protected ?int $lastInsertId = null;

    /**
     * @throws ManipulationException
     * @internal
     */
    protected function doCreate(RecordInterface $record): int
    {
        try {
            $fields = $this->getFields($record);
            $result = $this->connection->insert($this->getName(), $fields);

            $this->lastInsertId = (int) $this->connection->lastInsertId();

            return $result;
        } catch (DBALException $e) {
            throw new ManipulationException($e->getMessage(), 0, $e);
        }
    }

    /**
     * @throws ManipulationException
     * @internal
     */
    protected function doUpdate(RecordInterface $record): int
    {
        try {
            $fields = $this->getFields($record);
            $criteria = $this->getCriteria($fields);

            return $this->connection->update($this->getName(), $fields, $criteria);
        } catch (DBALException $e) {
            throw new ManipulationException($e->getMessage(), 0, $e);
        }
    }

    /**
     * @throws ManipulationException
     * @internal
     */
    protected function doDelete(RecordInterface $record): int
    {
        try {
            $fields = $this->getFields($record);
            $criteria = $this->getCriteria($fields);

            return $this->connection->delete($this->getName(), $criteria);
        } catch (DBALException $e) {
            throw new ManipulationException($e->getMessage(), 0, $e);
        }
    }

    public function getLastInsertId(): ?int
    {
        return $this->lastInsertId;
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
        $fields = $this->serializeFields($record->getProperties());
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
