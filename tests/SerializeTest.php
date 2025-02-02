<?php
/*
 * PSX is an open source PHP framework to develop RESTful APIs.
 * For the current version and information visit <https://phpsx.org>
 *
 * Copyright (c) Christoph Kappestein <christoph.kappestein@gmail.com>
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

use PSX\DateTime\LocalDate;
use PSX\DateTime\LocalDateTime;
use PSX\DateTime\LocalTime;
use PSX\Sql\TableManager;
use PSX\Sql\Tests\Generator\TableCommandTestTable;

/**
 * SerializeTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://phpsx.org
 */
class SerializeTest extends TableTestCase
{
    public function testSerialize()
    {
        $tableManager = new TableManager(getConnection());

        $table = $tableManager->getTable(TableCommandTestTable::class);
        $row   = $table->find(1);

        $this->assertIsString($row->getColBigint());
        $this->assertEquals('68719476735', $row->getColBigint());
        $this->assertIsResource($row->getColBinary());
        $this->assertEquals('foo', stream_get_contents($row->getColBinary()));
        $this->assertIsResource($row->getColBlob());
        $this->assertEquals('foobar', stream_get_contents($row->getColBlob()));
        $this->assertIsBool($row->getColBoolean());
        $this->assertEquals(true, $row->getColBoolean());
        $this->assertInstanceOf(LocalDateTime::class, $row->getColDatetime());
        $this->assertEquals('2015-01-21T23:59:59Z', $row->getColDatetime()->toString());
        $this->assertInstanceOf(LocalDateTime::class, $row->getColDatetimetz());
        $this->assertEquals('2015-01-21T23:59:59Z', $row->getColDatetimetz()->toString());
        $this->assertInstanceOf(LocalDate::class, $row->getColDate());
        $this->assertEquals('2015-01-21', $row->getColDate()->toString());
        $this->assertIsString($row->getColDecimal());
        $this->assertEquals('10', $row->getColDecimal());
        $this->assertIsFloat($row->getColFloat());
        $this->assertEquals(10.37, $row->getColFloat());
        $this->assertIsInt($row->getColInteger());
        $this->assertEquals(2147483647, $row->getColInteger());
        $this->assertIsInt($row->getColSmallint());
        $this->assertEquals(255, $row->getColSmallint());
        $this->assertIsString($row->getColText());
        $this->assertEquals('foobar', $row->getColText());
        $this->assertInstanceOf(LocalTime::class, $row->getColTime());
        $this->assertEquals('23:59:59', $row->getColTime()->toString());
        $this->assertIsString($row->getColString());
        $this->assertEquals('foobar', $row->getColString());

        $object = new \stdClass();
        $object->foo = 'bar';

        $this->assertInstanceOf(\stdClass::class, $row->getColJson());
        $this->assertEquals($object, $row->getColJson());
        $this->assertIsString($row->getColGuid());
        $this->assertEquals('ebe865da-4982-4353-bc44-dcdf7239e386', $row->getColGuid());
    }
}
