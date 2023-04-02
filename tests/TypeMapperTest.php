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

namespace PSX\Sql\Tests;

use Doctrine\DBAL\Types\Types;
use PHPUnit\Framework\TestCase;
use PSX\Sql\TableInterface;
use PSX\Sql\TypeMapper;

/**
 * TypeMapperTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://phpsx.org
 */
class TypeMapperTest extends TestCase
{
    protected $types = array(
        TableInterface::TYPE_SMALLINT => Types::SMALLINT,
        TableInterface::TYPE_INT      => Types::INTEGER,
        TableInterface::TYPE_BIGINT   => Types::BIGINT,
        TableInterface::TYPE_BOOLEAN  => Types::BOOLEAN,
        TableInterface::TYPE_DECIMAL  => Types::DECIMAL,
        TableInterface::TYPE_FLOAT    => Types::FLOAT,
        TableInterface::TYPE_DATE     => Types::DATE_MUTABLE,
        TableInterface::TYPE_DATETIME => Types::DATETIME_MUTABLE,
        TableInterface::TYPE_TIME     => Types::TIME_MUTABLE,
        TableInterface::TYPE_VARCHAR  => Types::STRING,
        TableInterface::TYPE_TEXT     => Types::TEXT,
        TableInterface::TYPE_BLOB     => Types::BLOB,
        TableInterface::TYPE_BINARY   => Types::BINARY,
        TableInterface::TYPE_JSON     => Types::JSON,
        TableInterface::TYPE_GUID     => Types::GUID,
    );

    public function testGetTypeByDoctrineType()
    {
        foreach ($this->types as $type => $class) {
            $this->assertEquals($type, TypeMapper::getTypeByDoctrineType($class));
        }
    }

    public function testGetDoctrineTypeByType()
    {
        foreach ($this->types as $type => $class) {
            $this->assertEquals($class, TypeMapper::getDoctrineTypeByType($type));
        }
    }
}
