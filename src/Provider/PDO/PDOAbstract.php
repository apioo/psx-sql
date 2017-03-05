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

namespace PSX\Sql\Provider\PDO;

use PDO;
use PSX\Sql\Provider\ParameterResolver;

/**
 * PDOAbstract
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    http://phpsx.org
 */
abstract class PDOAbstract
{
    protected $pdo;
    protected $sql;
    protected $parameters;
    protected $definition;
    protected $statement;

    public function __construct(PDO $pdo, $sql, array $parameters, $definition)
    {
        $this->pdo        = $pdo;
        $this->sql        = $sql;
        $this->parameters = $parameters;
        $this->definition = $definition;
    }

    public function getDefinition()
    {
        return $this->definition;
    }

    protected function getStatement($context = null)
    {
        if ($this->statement === null) {
            $this->statement = $this->pdo->prepare($this->sql);
        }

        $parameters = ParameterResolver::resolve($this->parameters, $context);

        foreach ($parameters as $name => $parameter) {
            $this->statement->bindValue($name, $parameter, self::getType($parameter));
        }

        $this->statement->execute();

        return $this->statement;
    }

    /**
     * Returns the fitting PDO type for the parameter
     * 
     * @internal
     * @param mixed $parameter
     * @return int
     */
    public static function getType($parameter)
    {
        if (is_bool($parameter)) {
            return \PDO::PARAM_BOOL;
        } elseif ($parameter === null) {
            return \PDO::PARAM_NULL;
        } elseif (is_int($parameter)) {
            return \PDO::PARAM_INT;
        } else {
            return \PDO::PARAM_STR;
        }
    }
}
