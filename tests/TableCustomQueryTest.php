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
 * TableCustomQueryTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    http://phpsx.org
 */
class TableCustomQueryTest extends \PHPUnit_Extensions_Database_TestCase
{
    public function getDataSet()
    {
        return $this->createFlatXMLDataSet(__DIR__ . '/provider_fixture.xml');
    }

    public function getConnection()
    {
        return $this->createDefaultDBConnection(getConnection()->getWrappedConnection(), '');
    }

    public function testGetAll()
    {
        $manager = new TableManager(getConnection());
        $table   = $manager->getTable(TestTableCustomQuery::class);

        $result = json_encode($table->getAll(), JSON_PRETTY_PRINT);
        $expect = <<<JSON
[
    {
        "id": "2",
        "authorId": "1",
        "title": "bar",
        "createDate": "2016-03-01 00:00:00",
        "authorName": "Foo Bar",
        "authorUri": "http:\/\/phpsx.org"
    },
    {
        "id": "1",
        "authorId": "1",
        "title": "foo",
        "createDate": "2016-03-01 00:00:00",
        "authorName": "Foo Bar",
        "authorUri": "http:\/\/phpsx.org"
    }
]
JSON;

        $this->assertJsonStringEqualsJsonString($expect, $result, $result);
    }

    public function testGetCount()
    {
        $manager = new TableManager(getConnection());
        $table   = $manager->getTable(TestTableCustomQuery::class);

        $this->assertEquals(2, $table->getCount());
    }
}
