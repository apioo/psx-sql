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

namespace PSX\Sql\Provider\DBAL;

use Doctrine\DBAL\Connection;
use PSX\Sql\Provider\DatabaseFactoryInterface;

/**
 * Factory
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    http://phpsx.org
 */
class Factory implements DatabaseFactoryInterface
{
    protected $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function newCollection($sql, array $parameters, $definition, $key = null, \Closure $filter = null)
    {
        return new Collection($this->connection, $sql, $parameters, $definition, $key);
    }

    public function newEntity($sql, array $parameters, $definition)
    {
        return new Entity($this->connection, $sql, $parameters, $definition);
    }

    public function newColumn($sql, array $parameters, $definition)
    {
        return new Column($this->connection, $sql, $parameters, $definition);
    }

    public function newValue($sql, array $parameters, $definition)
    {
        return new Value($this->connection, $sql, $parameters, $definition);
    }
}
