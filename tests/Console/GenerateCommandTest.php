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

namespace PSX\Sql\Tests\Console;

use PSX\Sql\Console\GenerateCommand;
use PSX\Sql\TableManager;
use PSX\Sql\Tests\TableTestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * GenerateCommandTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    http://phpsx.org
 */
class GenerateCommandTest extends TableTestCase
{
    /**
     * @var \PSX\Sql\TableManagerInterface
     */
    protected $manager;

    protected function setUp()
    {
        parent::setUp();

        $this->manager = new TableManager($this->connection);
    }

    public function testCommandPhp()
    {
        $command = new GenerateCommand(getConnection());

        $commandTester = new CommandTester($command);
        $commandTester->execute(array(
            'table'  => 'psx_table_command_test',
            'format' => 'php',
        ));

        $actual = $commandTester->getDisplay();

        if (getenv('DB') == 'mysql') {
            $expect = <<<PHP
<?php

namespace PSX\Generation;

class Test extends \PSX\Sql\TableAbstract
{
    public function getName()
    {
        return 'psx_table_command_test';
    }
    public function getColumns()
    {
        return array('id' => self::TYPE_INT | self::PRIMARY_KEY | self::AUTO_INCREMENT, 'col_bigint' => self::TYPE_BIGINT, 'col_binary' => self::TYPE_BINARY | 255, 'col_blob' => self::TYPE_BLOB, 'col_boolean' => self::TYPE_BOOLEAN, 'col_datetime' => self::TYPE_DATETIME, 'col_datetimetz' => self::TYPE_DATETIME, 'col_date' => self::TYPE_DATE, 'col_decimal' => self::TYPE_DECIMAL, 'col_float' => self::TYPE_FLOAT, 'col_integer' => self::TYPE_INT, 'col_smallint' => self::TYPE_SMALLINT, 'col_text' => self::TYPE_TEXT, 'col_time' => self::TYPE_TIME, 'col_string' => self::TYPE_VARCHAR | 255, 'col_array' => self::TYPE_ARRAY, 'col_object' => self::TYPE_OBJECT, 'col_json' => self::TYPE_JSON, 'col_guid' => self::TYPE_GUID | 36);
    }
}
PHP;
        } else {
            $expect = <<<PHP
<?php

namespace PSX\Generation;

class Test extends \PSX\Sql\TableAbstract
{
    public function getName()
    {
        return 'psx_table_command_test';
    }
    public function getColumns()
    {
        return array('id' => self::TYPE_INT | self::PRIMARY_KEY | self::AUTO_INCREMENT, 'col_bigint' => self::TYPE_BIGINT, 'col_binary' => self::TYPE_BLOB, 'col_blob' => self::TYPE_BLOB, 'col_boolean' => self::TYPE_BOOLEAN, 'col_datetime' => self::TYPE_DATETIME, 'col_datetimetz' => self::TYPE_DATETIME, 'col_date' => self::TYPE_DATE, 'col_decimal' => self::TYPE_DECIMAL, 'col_float' => self::TYPE_FLOAT, 'col_integer' => self::TYPE_INT, 'col_smallint' => self::TYPE_SMALLINT, 'col_text' => self::TYPE_TEXT, 'col_time' => self::TYPE_TIME, 'col_string' => self::TYPE_VARCHAR | 255, 'col_array' => self::TYPE_TEXT, 'col_object' => self::TYPE_TEXT, 'col_json' => self::TYPE_TEXT, 'col_guid' => self::TYPE_VARCHAR | 36);
    }
}
PHP;
        }

        $expect = str_replace(["\r\n", "\n", "\r"], "\n", $expect);
        $actual = str_replace(["\r\n", "\n", "\r"], "\n", $actual);

        $this->assertEquals($expect, $actual, $actual);
    }


    public function testCommandJsonSchema()
    {
        $command = new GenerateCommand(getConnection());

        $commandTester = new CommandTester($command);
        $commandTester->execute(array(
            'table'  => 'psx_table_command_test',
            'format' => 'jsonschema',
        ));

        $actual = $commandTester->getDisplay();

        if (getenv('DB') == 'mysql') {
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
            "type": "string",
            "maxLength": 255
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
        } else {
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
        }

        $this->assertJsonStringEqualsJsonString($expect, $actual, $actual);
    }
}
