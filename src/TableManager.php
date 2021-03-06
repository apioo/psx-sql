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

use Doctrine\DBAL\Connection;
use InvalidArgumentException;
use PSX\Sql\Table\ReaderInterface;

/**
 * TableManager
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    http://phpsx.org
 */
class TableManager implements TableManagerInterface
{
    /**
     * @var \Doctrine\DBAL\Connection
     */
    private $connection;

    /**
     * @var \PSX\Sql\Table\ReaderInterface
     */
    private $reader;

    /**
     * @var TableInterface[]
     */
    private $container;

    /**
     * @param \Doctrine\DBAL\Connection $connection
     * @param \PSX\Sql\Table\ReaderInterface $reader
     */
    public function __construct(Connection $connection, ReaderInterface $reader = null)
    {
        $this->connection = $connection;
        $this->reader     = $reader;
    }

    /**
     * @return \Doctrine\DBAL\Connection
     */
    public function getConnection(): Connection
    {
        return $this->connection;
    }

    /**
     * @inheritDoc
     */
    public function getTable(string $tableName)
    {
        if (isset($this->container[$tableName])) {
            return $this->container[$tableName];
        }

        if ($this->reader === null) {
            // we assume that $tableName is a class name of a TableInterface implementation
            if (!class_exists($tableName)) {
                throw new InvalidArgumentException('Provided table class does not exist');
            }

            $table = new $tableName($this);

            $this->container[$tableName] = $table;
        } else {
            $definition = $this->reader->getTableDefinition($tableName);

            $this->container[$tableName] = new Table($this,
                $definition->getName(),
                $definition->getColumns()
            );
        }

        return $this->container[$tableName];
    }
}
