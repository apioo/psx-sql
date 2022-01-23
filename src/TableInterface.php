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

use PSX\Sql\Exception\NoLastInsertIdAvailable;

/**
 * Represents a class which describes a table
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://phpsx.org
 */
interface TableInterface extends ViewInterface
{
    public const PRIMARY_KEY     = 0x10000000;
    public const AUTO_INCREMENT  = 0x20000000;
    public const IS_NULL         = 0x40000000;
    public const UNSIGNED        = 0x80000000;

    // integer
    public const TYPE_SMALLINT   = 0x100000;
    public const TYPE_INT        = 0x200000;
    public const TYPE_BIGINT     = 0x300000;
    public const TYPE_BOOLEAN    = 0x400000;

    // float
    public const TYPE_DECIMAL    = 0x500000;
    public const TYPE_FLOAT      = 0x600000;

    // date
    public const TYPE_DATE       = 0x700000;
    public const TYPE_DATETIME   = 0x800000;
    public const TYPE_TIME       = 0x900000;
    public const TYPE_INTERVAL   = 0x4000000;

    // string
    public const TYPE_VARCHAR    = 0xA00000;
    public const TYPE_TEXT       = 0xB00000;

    // binary
    public const TYPE_BINARY     = 0x2000000;
    public const TYPE_BLOB       = 0xC00000;

    // formats
    public const TYPE_ARRAY      = 0xD00000;
    public const TYPE_OBJECT     = 0xE00000;
    public const TYPE_JSON       = 0xF00000;
    public const TYPE_GUID       = 0x1000000;

    /**
     * Returns the name of the table
     */
    public function getName(): string;

    /**
     * Returns the columns of the table where the key is the name of the column and the value contains OR connected
     * information. I.e.:
     * <code>
     * array(
     *  'id'    => self::TYPE_INT | 10 | self::PRIMARY_KEY,
     *  'title' => self::TYPE_VARCHAR | 256
     * )
     * </code>
     *
     * For better understanding here a 32 bit integer representation of the example above:
     * <code>
     *             UNAP     T                L
     * array(      |||| |-------| |----------------------|
     *  'id'    => 0011 0000 0100 0000 0000 0000 0000 1010
     *  'title' => 0000 1100 0000 0000 0000 0001 0000 0000
     * )
     * </code>
     *
     * L: Length of the column max value is 0xFFFFF (decimal: 1048575)
     * T: Type of the column one of TYPE_* constant
     * P: Whether its a primary key
     * A: Whether its an auto increment value
     * N: Whether the column can be NULL
     * U: Whether the value is unsigned
     *
     * @return array<string, int>
     */
    public function getColumns(): array;

    /**
     * Returns the name of the primary key columns
     */
    public function getPrimaryKeys(): array;

    /**
     * Returns whether the table has the $column
     */
    public function hasColumn(string $column): bool;

    /**
     * Returns all column name for this table
     */
    public function getColumnNames(): array;

    /**
     * Returns the number of rows matching the given condition
     */
    public function getCount(?Condition $condition = null): int;

    /**
     * Start a transaction
     */
    public function beginTransaction(): void;

    /**
     * Commit a transaction
     */
    public function commit(): void;

    /**
     * Rollback a transaction
     */
    public function rollBack(): void;

    /**
     * @throws NoLastInsertIdAvailable
     */
    public function getLastInsertId(): int;
}
