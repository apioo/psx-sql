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

namespace PSX\Sql\Provider\DBAL;

use Doctrine\DBAL\Connection;
use PSX\Sql\Provider\PDO\PDOAbstract;

/**
 * DBALAbstract
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://phpsx.org
 */
abstract class DBALAbstract
{
    protected Connection $connection;
    protected string $sql;
    protected array $parameters;
    protected mixed $definition;

    public function __construct(Connection $connection, string $sql, array $parameters, mixed $definition)
    {
        $this->connection = $connection;
        $this->sql        = $sql;
        $this->parameters = $parameters;
        $this->definition = $definition;
    }

    public function getDefinition(): mixed
    {
        return $this->definition;
    }

    /**
     * Returns an array of PDO type corresponding to the parameter array
     */
    public static function getTypes(array $parameters): array
    {
        $types = [];
        foreach ($parameters as $parameter) {
            $types[] = PDOAbstract::getType($parameter);
        }
        return $types;
    }
}
