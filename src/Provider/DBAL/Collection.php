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

namespace PSX\Sql\Provider\DBAL;

use Doctrine\DBAL\Connection;
use PSX\Sql\Provider\ParameterResolver;
use PSX\Sql\Provider\ProviderCollectionInterface;

/**
 * Collection
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    http://phpsx.org
 */
class Collection extends DBALAbstract implements ProviderCollectionInterface
{
    protected $key;

    public function __construct(Connection $connection, $sql, array $parameters, array $definition, $key = null)
    {
        parent::__construct($connection, $sql, $parameters, $definition);

        $this->key = $key;
    }

    public function getKey()
    {
        return $this->key;
    }

    public function getResult($context = null)
    {
        return $this->connection->fetchAll(
            $this->sql,
            ParameterResolver::resolve($this->parameters, $context)
        );
    }
}
