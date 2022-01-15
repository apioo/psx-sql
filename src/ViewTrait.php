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

use PSX\Sql\Exception\BuilderException;
use PSX\Sql\Provider\ProviderCollectionInterface;
use PSX\Sql\Provider\ProviderColumnInterface;
use PSX\Sql\Provider\ProviderEntityInterface;
use PSX\Sql\Provider\ProviderValueInterface;

/**
 * ViewTrait
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://phpsx.org
 */
trait ViewTrait
{
    /**
     * @template T of ViewInterface
     * @psalm-param class-string<T> $tableName
     * @return T
     */
    protected function getTable(string $tableName): ViewInterface
    {
        return $this->tableManager->getTable($tableName);
    }

    protected function build(mixed $definition): mixed
    {
        return $this->builder->build($definition);
    }

    protected function doCollection(callable|string|array $source, array $arguments, array $definition, string|\Closure|null $key = null, ?\Closure $filter = null): ProviderCollectionInterface
    {
        return $this->builder->doCollection($source, $arguments, $definition, $key, $filter);
    }

    protected function doEntity(callable|string|array $source, array $arguments, array $definition): ProviderEntityInterface
    {
        return $this->builder->doEntity($source, $arguments, $definition);
    }

    protected function doColumn(mixed $source, array $arguments, mixed $definition): ProviderColumnInterface
    {
        return $this->builder->doColumn($source, $arguments, $definition);
    }

    protected function doValue(mixed $source, array $arguments, mixed $definition): ProviderValueInterface
    {
        return $this->builder->doValue($source, $arguments, $definition);
    }

    protected function fieldBoolean(string $value): Field\Boolean
    {
        return $this->builder->fieldBoolean($value);
    }

    protected function fieldCallback(string $key, \Closure $callback): Field\Callback
    {
        return $this->builder->fieldCallback($key, $callback);
    }

    protected function fieldCsv(string $key, string $delimiter = ','): Field\Csv
    {
        return $this->builder->fieldCsv($key, $delimiter);
    }

    protected function fieldDateTime(string $value): Field\DateTime
    {
        return $this->builder->fieldDateTime($value);
    }

    protected function fieldInteger(string $value): Field\Integer
    {
        return $this->builder->fieldInteger($value);
    }

    protected function fieldJson(string $value): Field\Json
    {
        return $this->builder->fieldJson($value);
    }

    protected function fieldNumber(string $value): Field\Number
    {
        return $this->builder->fieldNumber($value);
    }

    protected function fieldFormat(string $value, string $format): Field\Format
    {
        return $this->builder->fieldFormat($value, $format);
    }

    protected function fieldValue(mixed $value): Field\Value
    {
        return $this->builder->fieldValue($value);
    }
}
