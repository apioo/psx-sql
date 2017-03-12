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

namespace PSX\Sql\Tests;

use PSX\Record\Record;
use PSX\Sql\TableInterface;
use PSX\Sql\TableManager;

/**
 * TableComplexTest
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    http://phpsx.org
 */
class TableComplexTest extends \PHPUnit_Extensions_Database_TestCase
{
    /**
     * @var \Doctrine\DBAL\Connection
     */
    protected $connection;

    /**
     * @var \PSX\Sql\TableManagerInterface
     */
    protected $manager;

    protected function setUp()
    {
        parent::setUp();

        $this->connection = getConnection();
        $this->manager    = new TableManager($this->connection);
    }

    public function getDataSet()
    {
        return $this->createFlatXMLDataSet(__DIR__ . '/table_fixture.xml');
    }

    public function getConnection()
    {
        return $this->createDefaultDBConnection(getConnection()->getWrappedConnection(), '');
    }

    /**
     * @return TestTable
     */
    protected function getTable()
    {
        return $this->manager->getTable(TestTable::class);
    }

    public function testGetNestedResult()
    {
        $result = $this->getTable()->getNestedResult();
        $actual = json_encode($result, JSON_PRETTY_PRINT);
        $expect = <<<JSON
[
    {
        "id": 4,
        "title": "Blub",
        "author": {
            "id": "urn:profile:3",
            "date": "2013-04-29T16:56:32Z"
        },
        "count": 4,
        "tags": [
            "2013-04-29 16:56:32",
            "2013-04-29 16:56:32",
            "2013-04-29 16:56:32",
            "2013-04-29 16:56:32"
        ]
    },
    {
        "id": 3,
        "title": "Test",
        "author": {
            "id": "urn:profile:2",
            "date": "2013-04-29T16:56:32Z"
        },
        "count": 4,
        "tags": [
            "2013-04-29 16:56:32",
            "2013-04-29 16:56:32",
            "2013-04-29 16:56:32",
            "2013-04-29 16:56:32"
        ]
    },
    {
        "id": 2,
        "title": "Bar",
        "author": {
            "id": "urn:profile:1",
            "date": "2013-04-29T16:56:32Z"
        },
        "count": 4,
        "tags": [
            "2013-04-29 16:56:32",
            "2013-04-29 16:56:32",
            "2013-04-29 16:56:32",
            "2013-04-29 16:56:32"
        ]
    },
    {
        "id": 1,
        "title": "Foo",
        "author": {
            "id": "urn:profile:1",
            "date": "2013-04-29T16:56:32Z"
        },
        "note": {
            "comments": true,
            "title": "foobar"
        },
        "count": 4,
        "tags": [
            "2013-04-29 16:56:32",
            "2013-04-29 16:56:32",
            "2013-04-29 16:56:32",
            "2013-04-29 16:56:32"
        ]
    }
]
JSON;

        $this->assertJsonStringEqualsJsonString($expect, $actual, $actual);
    }

    public function testGetNestedResultKey()
    {
        $result = $this->getTable()->getNestedResultKey();
        $actual = json_encode($result, JSON_PRETTY_PRINT);
        $expect = <<<JSON
{
    "eccbc87e": {
        "id": 4,
        "title": "Blub",
        "author": {
            "id": "urn:profile:3",
            "date": "2013-04-29T16:56:32Z"
        }
    },
    "c81e728d": {
        "id": 3,
        "title": "Test",
        "author": {
            "id": "urn:profile:2",
            "date": "2013-04-29T16:56:32Z"
        }
    },
    "c4ca4238": {
        "id": 1,
        "title": "Foo",
        "author": {
            "id": "urn:profile:1",
            "date": "2013-04-29T16:56:32Z"
        },
        "note": {
            "comments": true,
            "title": "foobar"
        }
    }
}
JSON;

        $this->assertJsonStringEqualsJsonString($expect, $actual, $actual);
    }

    public function testGetNestedResultFilter()
    {
        $result = $this->getTable()->getNestedResultFilter();
        $actual = json_encode($result, JSON_PRETTY_PRINT);
        $expect = <<<JSON
[
    {
        "id": 2,
        "title": "Bar",
        "author": {
            "id": "urn:profile:1",
            "date": "2013-04-29T16:56:32Z"
        }
    },
    {
        "id": 1,
        "title": "Foo",
        "author": {
            "id": "urn:profile:1",
            "date": "2013-04-29T16:56:32Z"
        },
        "note": {
            "comments": true,
            "title": "foobar"
        }
    }
]
JSON;

        $this->assertJsonStringEqualsJsonString($expect, $actual, $actual);
    }

    public function testGetNestedResultFields()
    {
        $result = $this->getTable()->getNestedResultFields();
        $actual = json_encode($result, JSON_PRETTY_PRINT);
        $expect = <<<JSON
{
    "boolean": true,
    "callback": "bar",
    "csv": [
        "foo",
        "bar"
    ],
    "dateTime": "2017-03-05T00:00:00Z",
    "integer": 1,
    "json": {
        "foo": "bar"
    },
    "number": 12.34,
    "replace": "http:\/\/foo.com\/foo",
    "type": 1,
    "value": "bar"
}
JSON;

        $this->assertJsonStringEqualsJsonString($expect, $actual, $actual);
    }
}
