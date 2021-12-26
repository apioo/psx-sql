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

use Doctrine\DBAL\Types\Types;

/**
 * Maps doctrine to psx types
 *
 * @internal
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    http://phpsx.org
 */
class TypeMapper
{
    private const MAPPING = [
        TableInterface::TYPE_SMALLINT => Types::SMALLINT,
        TableInterface::TYPE_INT      => Types::INTEGER,
        TableInterface::TYPE_BIGINT   => Types::BIGINT,
        TableInterface::TYPE_BOOLEAN  => Types::BOOLEAN,
        TableInterface::TYPE_DECIMAL  => Types::DECIMAL,
        TableInterface::TYPE_FLOAT    => Types::FLOAT,
        TableInterface::TYPE_DATE     => Types::DATE_MUTABLE,
        TableInterface::TYPE_DATETIME => Types::DATETIME_MUTABLE,
        TableInterface::TYPE_INTERVAL => Types::DATEINTERVAL,
        TableInterface::TYPE_TIME     => Types::TIME_MUTABLE,
        TableInterface::TYPE_VARCHAR  => Types::STRING,
        TableInterface::TYPE_TEXT     => Types::TEXT,
        TableInterface::TYPE_BLOB     => Types::BLOB,
        TableInterface::TYPE_BINARY   => Types::BINARY,
        TableInterface::TYPE_ARRAY    => Types::ARRAY,
        TableInterface::TYPE_OBJECT   => Types::OBJECT,
        TableInterface::TYPE_JSON     => Types::JSON,
        TableInterface::TYPE_GUID     => Types::GUID,
    ];

    public static function getTypeByDoctrineType(string $name): int
    {
        $type = array_search($name, self::MAPPING);
        if ($type !== false) {
            return $type;
        } else {
            return TableInterface::TYPE_VARCHAR;
        }
    }

    public static function getDoctrineTypeByType(int $type): string
    {
        if (isset(self::MAPPING[$type & 0xFF00000])) {
            return self::MAPPING[$type & 0xFF00000];
        } else {
            return Types::STRING;
        }
    }
}
