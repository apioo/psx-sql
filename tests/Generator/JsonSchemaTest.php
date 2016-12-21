<?php
/*
 * PSX is a open source PHP framework to develop RESTful APIs.
 * For the current version and informations visit <http://phpsx.org>
 *
 * Copyright 2010-2016 Christoph Kappestein <k42b3.x@gmail.com>
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

namespace PSX\Sql\Tests\Generator;

use PSX\Sql\Generator;

/**
 * JsonSchemaTest
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    http://phpsx.org
 */
class JsonSchemaTest extends \PHPUnit_Extensions_Database_TestCase
{
    public function getDataSet()
    {
        return $this->createFlatXMLDataSet(__DIR__ . '/../table_fixture.xml');
    }

    public function getConnection()
    {
        return $this->createDefaultDBConnection(getConnection()->getWrappedConnection(), '');
    }

    public function testGenerate()
    {
        $table = getConnection()
            ->getSchemaManager()
            ->listTableDetails('psx_table_command_test');

        $generator = new Generator\JsonSchema();

        $actual = $generator->generate($table);
        $expect = <<<'JSON'
{
    "$schema": "http:\/\/json-schema.org\/draft-04\/schema#",
    "title": "psx_table_command_test",
    "type": "object",
    "properties": {
        "id": {
            "type": "integer"
        },
        "col_bigint": {
            "type": "integer"
        },
        "col_binary": {
            "type": "string"
        },
        "col_blob": {
            "type": "string"
        },
        "col_boolean": {
            "type": "boolean"
        },
        "col_datetime": {
            "type": "string",
            "format": "date-time"
        },
        "col_datetimetz": {
            "type": "string",
            "format": "date-time"
        },
        "col_date": {
            "type": "string",
            "format": "date"
        },
        "col_decimal": {
            "type": "number"
        },
        "col_float": {
            "type": "number"
        },
        "col_integer": {
            "type": "integer"
        },
        "col_smallint": {
            "type": "integer"
        },
        "col_text": {
            "type": "string"
        },
        "col_time": {
            "type": "string",
            "format": "time"
        },
        "col_string": {
            "type": "string",
            "maxLength": 255
        },
        "col_array": {
            "type": "string"
        },
        "col_object": {
            "type": "string"
        },
        "col_json": {
            "type": "string"
        },
        "col_guid": {
            "type": "string",
            "maxLength": 36
        }
    }
}
JSON;

        $this->assertJsonStringEqualsJsonString($expect, $actual, $actual);
    }
}
