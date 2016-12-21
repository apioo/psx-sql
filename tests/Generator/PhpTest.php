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
 * PhpTest
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    http://phpsx.org
 */
class PhpTest extends \PHPUnit_Extensions_Database_TestCase
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

        $generator = new Generator\Php();

        if (getenv('DB') == 'mysql') {
            $columns = 'array(\'id\' => self::TYPE_INT | self::PRIMARY_KEY | self::AUTO_INCREMENT, \'col_bigint\' => self::TYPE_BIGINT, \'col_binary\' => self::TYPE_BINARY | 255, \'col_blob\' => self::TYPE_BLOB, \'col_boolean\' => self::TYPE_BOOLEAN, \'col_datetime\' => self::TYPE_DATETIME, \'col_datetimetz\' => self::TYPE_DATETIME, \'col_date\' => self::TYPE_DATE, \'col_decimal\' => self::TYPE_DECIMAL, \'col_float\' => self::TYPE_FLOAT, \'col_integer\' => self::TYPE_INT, \'col_smallint\' => self::TYPE_SMALLINT, \'col_text\' => self::TYPE_TEXT, \'col_time\' => self::TYPE_TIME, \'col_string\' => self::TYPE_VARCHAR | 255, \'col_array\' => self::TYPE_ARRAY, \'col_object\' => self::TYPE_OBJECT, \'col_json\' => self::TYPE_JSON, \'col_guid\' => self::TYPE_GUID | 36)';
        } else {
            $columns = 'array(\'id\' => self::TYPE_INT | self::PRIMARY_KEY | self::AUTO_INCREMENT, \'col_bigint\' => self::TYPE_BIGINT, \'col_binary\' => self::TYPE_BLOB, \'col_blob\' => self::TYPE_BLOB, \'col_boolean\' => self::TYPE_BOOLEAN, \'col_datetime\' => self::TYPE_DATETIME, \'col_datetimetz\' => self::TYPE_DATETIME, \'col_date\' => self::TYPE_DATE, \'col_decimal\' => self::TYPE_DECIMAL, \'col_float\' => self::TYPE_FLOAT, \'col_integer\' => self::TYPE_INT, \'col_smallint\' => self::TYPE_SMALLINT, \'col_text\' => self::TYPE_TEXT, \'col_time\' => self::TYPE_TIME, \'col_string\' => self::TYPE_VARCHAR | 255, \'col_array\' => self::TYPE_TEXT, \'col_object\' => self::TYPE_TEXT, \'col_json\' => self::TYPE_TEXT, \'col_guid\' => self::TYPE_VARCHAR | 36)';
        }

        $actual = $generator->generate($table);
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
        return {$columns};
    }
}
PHP;

        $expect = str_replace(["\r\n", "\n", "\r"], "\n", $expect);
        $actual = str_replace(["\r\n", "\n", "\r"], "\n", $actual);

        $this->assertEquals($expect, $actual, $actual);
    }
}
