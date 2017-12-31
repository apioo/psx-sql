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
        return $this->builder->doCollection($source, $arguments, $definition, $key, $filter);
    }

    /**
     * @param mixed $source
     * @param array $arguments
     * @param array $definition
     * @return \PSX\Sql\Provider\ProviderEntityInterface
     */
    protected function doEntity($source, array $arguments, array $definition)
    {
        return $this->builder->doEntity($source, $arguments, $definition);
    }

    /**
     * @param mixed $source
     * @param array $arguments
     * @param mixed $definition
     * @return \PSX\Sql\Provider\ProviderColumnInterface
     */
    protected function doColumn($source, array $arguments, $definition)
    {
        return $this->builder->doColumn($source, $arguments, $definition);
    }

    /**
     * @param mixed $source
     * @param array $arguments
     * @param mixed $definition
     * @return \PSX\Sql\Provider\ProviderValueInterface
     */
    protected function doValue($source, array $arguments, $definition)
    {
        return $this->builder->doValue($source, $arguments, $definition);
    }

    /**
     * @param string $value
     * @return \PSX\Sql\Field\Boolean
     */
    protected function fieldBoolean($value)
    {
        return $this->builder->fieldBoolean($value);
    }

    /**
     * @param string $key
     * @param \Closure $callback
     * @return \PSX\Sql\Field\Callback
     */
    protected function fieldCallback($key, \Closure $callback)
    {
        return $this->builder->fieldCallback($key, $callback);
    }

    /**
     * @param string $key
     * @param string $delimiter
     * @return \PSX\Sql\Field\Csv
     */
    protected function fieldCsv($key, $delimiter = ',')
    {
        return $this->builder->fieldCsv($key, $delimiter);
    }

    /**
     * @param string $value
     * @return \PSX\Sql\Field\DateTime
     */
    protected function fieldDateTime($value)
    {
        return $this->builder->fieldDateTime($value);
    }

    /**
     * @param string $value
     * @return \PSX\Sql\Field\Integer
     */
    protected function fieldInteger($value)
    {
        return $this->builder->fieldInteger($value);
    }

    /**
     * @param string $value
     * @return \PSX\Sql\Field\Json
     */
    protected function fieldJson($value)
    {
        return $this->builder->fieldJson($value);
    }

    /**
     * @param string $value
     * @return \PSX\Sql\Field\Number
     */
    protected function fieldNumber($value)
    {
        return $this->builder->fieldNumber($value);
    }

    /**
     * @param string $value
     * @return \PSX\Sql\Field\Replace
     */
    protected function fieldReplace($value)
    {
        return $this->builder->fieldReplace($value);
    }

    /**
     * @param string $value
     * @param integer $type
     * @return \PSX\Sql\Field\Type
     */
    protected function fieldType($value, $type)
    {
        return $this->builder->fieldType($value, $type);
    }

    /**
     * @param string $value
     * @return \PSX\Sql\Field\Value
     */
    protected function fieldValue($value)
    {
        return $this->builder->fieldValue($value);
    }
}
