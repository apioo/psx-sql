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

/**
 * ViewTrait
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    http://phpsx.org
 */
trait ViewTrait
{
    /**
     * @param string $tableName
     * @return \PSX\Sql\TableInterface
     */
    protected function getTable($tableName)
    {
        return $this->tableManager->getTable($tableName);
    }

    /**
     * @param array $definition
     * @return mixed
     */
    protected function build($definition)
    {
        return $this->builder->build($definition);
    }

    /**
     * @param mixed $source
     * @param array $arguments
     * @param array $definition
     * @param string|null $key
     * @param \Closure|null $filter
     * @return \PSX\Sql\Provider\ProviderCollectionInterface
     */
    protected function doCollection($source, array $arguments, array $definition, $key = null, \Closure $filter = null)
    {
        if (is_callable($source)) {
            return new Provider\Callback\Collection($source, $arguments, $definition, $key, $filter);
        } elseif (is_string($source)) {
            return new Provider\DBAL\Collection($this->connection, $source, $arguments, $definition, $key, $filter);
        } elseif (is_array($source)) {
            return new Provider\Map\Collection($source, $definition, $key, $filter);
        } else {
            throw new InvalidArgumentException('Source must be either a callable, string or array');
        }
    }

    /**
     * @param mixed $source
     * @param array $arguments
     * @param array $definition
     * @return \PSX\Sql\Provider\ProviderEntityInterface
     */
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

    /**
     * @param mixed $source
     * @param array $arguments
     * @param mixed $definition
     * @return \PSX\Sql\Provider\ProviderColumnInterface
     */
    protected function doColumn($source, array $arguments, $definition)
    {
        if (is_callable($source)) {
            return new Provider\Callback\Column($source, $arguments, $definition);
        } elseif (is_string($source)) {
            return new Provider\DBAL\Column($this->connection, $source, $arguments, $definition);
        } elseif (is_array($source)) {
            return new Provider\Map\Column($source, $definition);
        } else {
            throw new InvalidArgumentException('Source must be either a callable, string or array');
        }
    }

    /**
     * @param mixed $source
     * @param array $arguments
     * @param mixed $definition
     * @return \PSX\Sql\Provider\ProviderValueInterface
     */
    protected function doValue($source, array $arguments, $definition)
    {
        if (is_callable($source)) {
            return new Provider\Callback\Value($source, $arguments, $definition);
        } elseif (is_string($source)) {
            return new Provider\DBAL\Value($this->connection, $source, $arguments, $definition);
        } elseif (is_array($source)) {
            return new Provider\Map\Value($source, $definition);
        } else {
            throw new InvalidArgumentException('Source must be either a callable, string or array');
        }
    }

    /**
     * @param string $value
     * @return \PSX\Sql\Field\Boolean
     */
    protected function fieldBoolean($value)
    {
        return new Field\Boolean($value);
    }

    /**
     * @param string $key
     * @param \Closure $callback
     * @return \PSX\Sql\Field\Callback
     */
    protected function fieldCallback($key, \Closure $callback)
    {
        return new Field\Callback($key, $callback);
    }

    /**
     * @param string $key
     * @param string $delimiter
     * @return \PSX\Sql\Field\Csv
     */
    protected function fieldCsv($key, $delimiter = ',')
    {
        return new Field\Csv($key, $delimiter);
    }

    /**
     * @param string $value
     * @return \PSX\Sql\Field\DateTime
     */
    protected function fieldDateTime($value)
    {
        return new Field\DateTime($value);
    }

    /**
     * @param string $value
     * @return \PSX\Sql\Field\Integer
     */
    protected function fieldInteger($value)
    {
        return new Field\Integer($value);
    }

    /**
     * @param string $value
     * @return \PSX\Sql\Field\Json
     */
    protected function fieldJson($value)
    {
        return new Field\Json($value);
    }

    /**
     * @param string $value
     * @return \PSX\Sql\Field\Number
     */
    protected function fieldNumber($value)
    {
        return new Field\Number($value);
    }

    /**
     * @param string $value
     * @return \PSX\Sql\Field\Replace
     */
    protected function fieldReplace($value)
    {
        return new Field\Replace($value);
    }

    /**
     * @param string $value
     * @param integer $type
     * @return \PSX\Sql\Field\Type
     */
    protected function fieldType($value, $type)
    {
        return new Field\Type($value, $this->connection, $type);
    }

    /**
     * @param string $value
     * @return \PSX\Sql\Field\Value
     */
    protected function fieldValue($value)
    {
        return new Field\Value($value);
    }
}
