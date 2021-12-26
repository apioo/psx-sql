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

/**
 * TableQueryInterface
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://phpsx.org
 *
 * @template T
 */
interface TableQueryInterface
{
    /**
     * @return iterable<T>
     */
    public function findAll(?Condition $condition = null, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null, ?Fields $fields = null) : iterable;

    /**
     * @return iterable<T>
     */
    public function findBy(Condition $condition, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null, ?Fields $fields = null) : iterable;

    /**
     * @return T|null
     */
    public function findOneBy(Condition $condition, ?Fields $fields = null): mixed;

    /**
     * Finds a database record by its primary key, normally this is simply the id column but the method supports also
     * combined primary keys then you can pass multiple values
     *
     * @return T|null
     */
    public function find(...$identifiers): mixed;

    /**
     * Returns all available column names of this table
     */
    public function getColumnNames(): array;

    /**
     * Returns the number of rows matching the given condition in the resultset
     */
    public function getCount(?Condition $condition = null): int;
}
