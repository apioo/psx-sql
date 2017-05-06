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

namespace PSX\Sql\Tests;

use Doctrine\DBAL\Types\Type;
use PSX\Sql\TableInterface;
use PSX\Sql\TypeMapper;

/**
 * TypeMapperTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    http://phpsx.org
 */
class TypeMapperTest extends \PHPUnit_Framework_TestCase
{
    protected $types = array(
        TableInterface::TYPE_SMALLINT => Type::SMALLINT,
        TableInterface::TYPE_INT      => Type::INTEGER,
        TableInterface::TYPE_BIGINT   => Type::BIGINT,
        TableInterface::TYPE_BOOLEAN  => Type::BOOLEAN,
        TableInterface::TYPE_DECIMAL  => Type::DECIMAL,
        TableInterface::TYPE_FLOAT    => Type::FLOAT,
        TableInterface::TYPE_DATE     => Type::DATE,
        TableInterface::TYPE_DATETIME => Type::DATETIME,
        TableInterface::TYPE_TIME     => Type::TIME,
        TableInterface::TYPE_VARCHAR  => Type::STRING,
        TableInterface::TYPE_TEXT     => Type::TEXT,
        TableInterface::TYPE_BLOB     => Type::BLOB,
        TableInterface::TYPE_BINARY   => Type::BINARY,
        TableInterface::TYPE_ARRAY    => Type::TARRAY,
        TableInterface::TYPE_OBJECT   => Type::OBJECT,
        TableInterface::TYPE_JSON     => Type::JSON_ARRAY,
        TableInterface::TYPE_GUID     => Type::GUID,
    );

    public function testGetTypeByDoctrineType()
    {
        foreach ($this->types as $type => $name) {
            $this->assertEquals($type, TypeMapper::getTypeByDoctrineType($name));
        }
    }

    public function testGetDoctrineTypeByType()
    {
        foreach ($this->types as $type => $name) {
            $this->assertEquals($name, TypeMapper::getDoctrineTypeByType($type));
        }
    }
}
