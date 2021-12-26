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

use PSX\Record\RecordInterface;

/**
 * TableQueryInterface
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://phpsx.org
 */
interface TableQueryInterface
{
    /**
     * Returns an array of records matching the conditions
     */
    public function getAll(?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null, ?Condition $condition = null, ?Fields $fields = null): iterable;

    /**
     * Returns an array of records matching the condition
     */
    public function getBy(Condition $condition, ?Fields $fields = null, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null): iterable;

    /**
     * Returns an record by the condition
     */
    public function getOneBy(Condition $condition, ?Fields $fields = null): ?RecordInterface;

    /**
     * Returns an record by the primary key
     */
    public function get(string|int $id, ?Fields $fields = null): ?RecordInterface;

    /**
     * Returns all available fields of this handler
     */
    public function getSupportedFields(): array;

    /**
     * Returns the number of rows matching the given condition in the resultset
     */
    public function getCount(?Condition $condition = null): int;
}
