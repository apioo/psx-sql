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

use PSX\Sql\TableManager;

/**
 * SerializeTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    http://phpsx.org
 */
class SerializeTest extends \PHPUnit_Extensions_Database_TestCase
{
    public function getDataSet()
    {
        return $this->createFlatXMLDataSet(__DIR__ . '/table_fixture.xml');
    }

    public function getConnection()
    {
        return $this->createDefaultDBConnection(getConnection()->getWrappedConnection(), '');
    }

    public function testSerialize()
    {
        $tableManager = new TableManager(getConnection());

        /** @var \PSX\Sql\Tests\TestTableCommand $table */
        $table = $tableManager->getTable('PSX\Sql\Tests\TestTableCommand');
        $row   = $table->get(1);

        $this->assertInternalType('string', $row->col_bigint);
        $this->assertEquals('68719476735', $row->col_bigint);
        $this->assertInternalType('resource', $row->col_binary);
        $this->assertEquals('foo', stream_get_contents($row->col_binary));
        $this->assertInternalType('resource', $row->col_blob);
        $this->assertEquals('foobar', stream_get_contents($row->col_blob));
        $this->assertInternalType('boolean', $row->col_boolean);
        $this->assertEquals(true, $row->col_boolean);
        $this->assertInstanceOf('DateTime', $row->col_datetime);
        $this->assertEquals('2015-01-21 23:59:59', $row->col_datetime->format('Y-m-d H:i:s'));
        $this->assertEquals('UTC', $row->col_datetime->getTimezone()->getName());
        $this->assertInstanceOf('DateTime', $row->col_datetimetz);
        $this->assertEquals('2015-01-21 23:59:59', $row->col_datetimetz->format('Y-m-d H:i:s'));
        $this->assertInstanceOf('DateTime', $row->col_date);
        $this->assertEquals('2015-01-21', $row->col_date->format('Y-m-d'));
        $this->assertInternalType('string', $row->col_decimal);
        $this->assertEquals('10', $row->col_decimal);
        $this->assertInternalType('float', $row->col_float);
        $this->assertEquals(10.37, $row->col_float);
        $this->assertInternalType('integer', $row->col_integer);
        $this->assertEquals(2147483647, $row->col_integer);
        $this->assertInternalType('integer', $row->col_smallint);
        $this->assertEquals(255, $row->col_smallint);
        $this->assertInternalType('string', $row->col_text);
        $this->assertEquals('foobar', $row->col_text);
        $this->assertInstanceOf('DateTime', $row->col_time);
        $this->assertEquals('23:59:59', $row->col_time->format('H:i:s'));
        $this->assertInternalType('string', $row->col_string);
        $this->assertEquals('foobar', $row->col_string);

        $array  = array('foo' => 'bar');
        $object = new \stdClass();
        $object->foo = 'bar';

        $this->assertInternalType('array', $row->col_array);
        $this->assertEquals($array, $row->col_array);
        $this->assertInstanceOf('stdClass', $row->col_object);
        $this->assertEquals($object, $row->col_object);
        $this->assertInstanceOf('stdClass', $row->col_json);
        $this->assertEquals($object, $row->col_json);
        $this->assertInternalType('string', $row->col_guid);
        $this->assertEquals('ebe865da-4982-4353-bc44-dcdf7239e386', $row->col_guid);
    }
}
