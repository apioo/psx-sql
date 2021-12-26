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

use PSX\Record\Record;
use PSX\Record\RecordInterface;
use PSX\Sql\Condition;
use PSX\Sql\Fields;
use PSX\Sql\Sql;
use PSX\Sql\TableQueryInterface;

/**
 * TableQueryTestTrait
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    http://phpsx.org
 */
trait TableQueryTestTrait
{
    public function testGetAll()
    {
        $table = $this->getTable();

        if (!$table instanceof TableQueryInterface) {
            $this->markTestSkipped('Table not a query interface');
        }

        $result = $table->getAll();

        $this->assertEquals(true, is_array($result));
        $this->assertEquals(4, count($result));

        $expect = array(
            new Record(array(
                'id' => 4,
                'userId' => 3,
                'title' => 'blub',
                'date' => new \DateTime('2013-04-29 16:56:32'),
            )),
            new Record(array(
                'id' => 3,
                'userId' => 2,
                'title' => 'test',
                'date' => new \DateTime('2013-04-29 16:56:32'),
            )),
            new Record(array(
                'id' => 2,
                'userId' => 1,
                'title' => 'bar',
                'date' => new \DateTime('2013-04-29 16:56:32'),
            )),
            new Record(array(
                'id' => 1,
                'userId' => 1,
                'title' => 'foo',
                'date' => new \DateTime('2013-04-29 16:56:32'),
            )),
        );

        $this->assertEquals($expect, $result);
    }

    public function testGetAllStartIndex()
    {
        $table = $this->getTable();

        if (!$table instanceof TableQueryInterface) {
            $this->markTestSkipped('Table not a query interface');
        }

        $result = $table->getAll(3);

        $this->assertEquals(true, is_array($result));
        $this->assertEquals(1, count($result));

        $expect = array(
            new Record(array(
                'id' => 1,
                'userId' => 1,
                'title' => 'foo',
                'date' => new \DateTime('2013-04-29 16:56:32'),
            )),
        );

        $this->assertEquals($expect, $result);
    }

    public function testGetAllCount()
    {
        $table = $this->getTable();

        if (!$table instanceof TableQueryInterface) {
            $this->markTestSkipped('Table not a query interface');
        }

        $result = $table->getAll(0, 2);

        $this->assertEquals(true, is_array($result));
        $this->assertEquals(2, count($result));

        $expect = array(
            new Record(array(
                'id' => 4,
                'userId' => 3,
                'title' => 'blub',
                'date' => new \DateTime('2013-04-29 16:56:32'),
            )),
            new Record(array(
                'id' => 3,
                'userId' => 2,
                'title' => 'test',
                'date' => new \DateTime('2013-04-29 16:56:32'),
            )),
        );

        $this->assertEquals($expect, $result);
    }

    public function testGetAllStartIndexAndCountDefault()
    {
        $table = $this->getTable();

        if (!$table instanceof TableQueryInterface) {
            $this->markTestSkipped('Table not a query interface');
        }

        $result = $table->getAll(2, 2);

        $this->assertEquals(true, is_array($result));
        $this->assertEquals(2, count($result));

        $expect = array(
            new Record(array(
                'id' => 2,
                'userId' => 1,
                'title' => 'bar',
                'date' => new \DateTime('2013-04-29 16:56:32'),
            )),
            new Record(array(
                'id' => 1,
                'userId' => 1,
                'title' => 'foo',
                'date' => new \DateTime('2013-04-29 16:56:32'),
            )),
        );

        $this->assertEquals($expect, $result);
    }

    public function testGetAllStartIndexAndCountDesc()
    {
        $table = $this->getTable();

        if (!$table instanceof TableQueryInterface) {
            $this->markTestSkipped('Table not a query interface');
        }

        $result = $table->getAll(2, 2, 'id', Sql::SORT_DESC);

        $this->assertEquals(true, is_array($result));
        $this->assertEquals(2, count($result));

        $expect = array(
            new Record(array(
                'id' => 2,
                'userId' => 1,
                'title' => 'bar',
                'date' => new \DateTime('2013-04-29 16:56:32'),
            )),
            new Record(array(
                'id' => 1,
                'userId' => 1,
                'title' => 'foo',
                'date' => new \DateTime('2013-04-29 16:56:32'),
            )),
        );

        $this->assertEquals($expect, $result);
    }

    public function testGetAllStartIndexAndCountAsc()
    {
        $table = $this->getTable();

        if (!$table instanceof TableQueryInterface) {
            $this->markTestSkipped('Table not a query interface');
        }

        $result = $table->getAll(2, 2, 'id', Sql::SORT_ASC);

        $this->assertEquals(true, is_array($result));
        $this->assertEquals(2, count($result));

        $expect = array(
            new Record(array(
                'id' => 3,
                'userId' => 2,
                'title' => 'test',
                'date' => new \DateTime('2013-04-29 16:56:32'),
            )),
            new Record(array(
                'id' => 4,
                'userId' => 3,
                'title' => 'blub',
                'date' => new \DateTime('2013-04-29 16:56:32'),
            )),
        );

        $this->assertEquals($expect, $result);
    }

    public function testGetAllSortDesc()
    {
        $table = $this->getTable();

        if (!$table instanceof TableQueryInterface) {
            $this->markTestSkipped('Table not a query interface');
        }

        $result = $table->getAll(0, 2, 'id', Sql::SORT_DESC);

        $this->assertEquals(true, is_array($result));
        $this->assertEquals(2, count($result));

        $expect = array(
            new Record(array(
                'id' => 4,
                'userId' => 3,
                'title' => 'blub',
                'date' => new \DateTime('2013-04-29 16:56:32'),
            )),
            new Record(array(
                'id' => 3,
                'userId' => 2,
                'title' => 'test',
                'date' => new \DateTime('2013-04-29 16:56:32'),
            )),
        );

        $this->assertEquals($expect, $result);

        foreach ($result as $row) {
            $this->assertTrue($row->id != null);
            $this->assertTrue($row->title != null);
        }

        // check order
        $this->assertEquals(4, $result[0]->id);
        $this->assertEquals(3, $result[1]->id);
    }

    public function testGetAllSortAsc()
    {
        $table = $this->getTable();

        if (!$table instanceof TableQueryInterface) {
            $this->markTestSkipped('Table not a query interface');
        }

        $result = $table->getAll(0, 2, 'id', Sql::SORT_ASC);

        $this->assertEquals(true, is_array($result));
        $this->assertEquals(2, count($result));

        $expect = array(
            new Record(array(
                'id' => 1,
                'userId' => 1,
                'title' => 'foo',
                'date' => new \DateTime('2013-04-29 16:56:32'),
            )),
            new Record(array(
                'id' => 2,
                'userId' => 1,
                'title' => 'bar',
                'date' => new \DateTime('2013-04-29 16:56:32'),
            )),
        );

        $this->assertEquals($expect, $result);
    }

    public function testGetAllCondition()
    {
        $table = $this->getTable();

        if (!$table instanceof TableQueryInterface) {
            $this->markTestSkipped('Table not a query interface');
        }

        $con    = new Condition(array('userId', '=', 1));
        $result = $table->getAll(0, 16, 'id', Sql::SORT_DESC, $con);

        $this->assertEquals(true, is_array($result));
        $this->assertEquals(2, count($result));

        $expect = array(
            new Record(array(
                'id' => 2,
                'userId' => 1,
                'title' => 'bar',
                'date' => new \DateTime('2013-04-29 16:56:32'),
            )),
            new Record(array(
                'id' => 1,
                'userId' => 1,
                'title' => 'foo',
                'date' => new \DateTime('2013-04-29 16:56:32'),
            )),
        );

        $this->assertEquals($expect, $result);
    }

    public function testGetAllConditionAndConjunction()
    {
        $table = $this->getTable();

        if (!$table instanceof TableQueryInterface) {
            $this->markTestSkipped('Table not a query interface');
        }

        $con = new Condition();
        $con->add('userId', '=', 1, 'AND');
        $con->add('userId', '=', 3);
        $result = $table->getAll(0, 16, 'id', Sql::SORT_DESC, $con);

        $this->assertEquals(true, is_array($result));
        $this->assertEquals(0, count($result));

        // check and condition with result
        $con = new Condition();
        $con->add('userId', '=', 1, 'AND');
        $con->add('title', '=', 'foo');
        $result = $table->getAll(0, 16, 'id', Sql::SORT_DESC, $con);

        $this->assertEquals(true, is_array($result));
        $this->assertEquals(1, count($result));

        $expect = array(
            new Record(array(
                'id' => 1,
                'userId' => 1,
                'title' => 'foo',
                'date' => new \DateTime('2013-04-29 16:56:32'),
            )),
        );

        $this->assertEquals($expect, $result);
    }

    public function testGetAllConditionOrConjunction()
    {
        $table = $this->getTable();

        if (!$table instanceof TableQueryInterface) {
            $this->markTestSkipped('Table not a query interface');
        }

        $con = new Condition();
        $con->add('userId', '=', 1, 'OR');
        $con->add('userId', '=', 3);
        $result = $table->getAll(0, 16, 'id', Sql::SORT_DESC, $con);

        $this->assertEquals(true, is_array($result));
        $this->assertEquals(3, count($result));

        $expect = array(
            new Record(array(
                'id' => 4,
                'userId' => 3,
                'title' => 'blub',
                'date' => new \DateTime('2013-04-29 16:56:32'),
            )),
            new Record(array(
                'id' => 2,
                'userId' => 1,
                'title' => 'bar',
                'date' => new \DateTime('2013-04-29 16:56:32'),
            )),
            new Record(array(
                'id' => 1,
                'userId' => 1,
                'title' => 'foo',
                'date' => new \DateTime('2013-04-29 16:56:32'),
            )),
        );

        $this->assertEquals($expect, $result);
    }

    public function testGetAllFieldWhitelist()
    {
        $table = $this->getTable();

        if (!$table instanceof TableQueryInterface) {
            $this->markTestSkipped('Table not a query interface');
        }

        $result = $table->getAll(0, 2, 'id', Sql::SORT_DESC, null, Fields::whitelist(['id', 'title']));

        $this->assertEquals(true, is_array($result));
        $this->assertEquals(2, count($result));

        $expect = array(
            new Record(array(
                'id' => 4,
                'title' => 'blub',
            )),
            new Record(array(
                'id' => 3,
                'title' => 'test',
            )),
        );

        $this->assertEquals($expect, $result);
    }

    public function testGetAllFieldBlacklist()
    {
        $table = $this->getTable();

        if (!$table instanceof TableQueryInterface) {
            $this->markTestSkipped('Table not a query interface');
        }

        $result = $table->getAll(0, 2, 'id', Sql::SORT_DESC, null, Fields::blacklist(['id', 'title']));

        $this->assertEquals(true, is_array($result));
        $this->assertEquals(2, count($result));

        $expect = array(
            new Record(array(
                'userId' => 3,
                'date' => new \DateTime('2013-04-29 16:56:32'),
            )),
            new Record(array(
                'userId' => 2,
                'date' => new \DateTime('2013-04-29 16:56:32'),
            )),
        );

        $this->assertEquals($expect, $result);
    }

    public function testGetBy()
    {
        $table = $this->getTable();

        if (!$table instanceof TableQueryInterface) {
            $this->markTestSkipped('Table not a query interface');
        }

        $result = $table->getBy(new Condition(['userId', '=', 1]));

        $this->assertEquals(true, is_array($result));
        $this->assertEquals(2, count($result));

        $expect = array(
            new Record(array(
                'id' => 2,
                'userId' => 1,
                'title' => 'bar',
                'date' => new \DateTime('2013-04-29 16:56:32'),
            )),
            new Record(array(
                'id' => 1,
                'userId' => 1,
                'title' => 'foo',
                'date' => new \DateTime('2013-04-29 16:56:32'),
            )),
        );

        $this->assertEquals($expect, $result);
    }

    public function testGetByFieldWhitelist()
    {
        $table = $this->getTable();

        if (!$table instanceof TableQueryInterface) {
            $this->markTestSkipped('Table not a query interface');
        }

        $result = $table->getBy(new Condition(['userId', '=', 1]), Fields::whitelist(['id', 'title']));

        $this->assertEquals(true, is_array($result));
        $this->assertEquals(2, count($result));

        $expect = array(
            new Record(array(
                'id' => 2,
                'title' => 'bar',
            )),
            new Record(array(
                'id' => 1,
                'title' => 'foo',
            )),
        );

        $this->assertEquals($expect, $result);
    }

    public function testGetByStartIndexCountOrder()
    {
        $table = $this->getTable();

        if (!$table instanceof TableQueryInterface) {
            $this->markTestSkipped('Table not a query interface');
        }

        $result = $table->getBy(new Condition(['userId', '=', 1]), null, 0, 1, 'id', Sql::SORT_ASC);

        $this->assertEquals(true, is_array($result));
        $this->assertEquals(1, count($result));

        $expect = array(
            new Record(array(
                'id' => 1,
                'userId' => 1,
                'title' => 'foo',
                'date' => new \DateTime('2013-04-29 16:56:32'),
            )),
        );

        $this->assertEquals($expect, $result);
    }

    public function testGetOneBy()
    {
        $table = $this->getTable();

        if (!$table instanceof TableQueryInterface) {
            $this->markTestSkipped('Table not a query interface');
        }

        $row = $table->getOneBy(new Condition(['id', '=', 1]));

        $expect = array(
            new Record(array(
                'id' => 1,
                'userId' => 1,
                'title' => 'foo',
                'date' => new \DateTime('2013-04-29 16:56:32'),
            )),
        );

        $this->assertEquals($expect, array($row));
    }

    public function testGetOneByFieldWhitelist()
    {
        $table = $this->getTable();

        if (!$table instanceof TableQueryInterface) {
            $this->markTestSkipped('Table not a query interface');
        }

        $row = $table->getOneBy(new Condition(['id', '=', 1]), Fields::whitelist(['id', 'title']));

        $expect = array(
            new Record(array(
                'id' => 1,
                'title' => 'foo',
            )),
        );

        $this->assertEquals($expect, array($row));
    }

    public function testGet()
    {
        $table = $this->getTable();

        if (!$table instanceof TableQueryInterface) {
            $this->markTestSkipped('Table not a query interface');
        }

        $row = $table->get(1);

        $expect = array(
            new Record(array(
                'id' => 1,
                'userId' => 1,
                'title' => 'foo',
                'date' => new \DateTime('2013-04-29 16:56:32'),
            )),
        );

        $this->assertEquals($expect, array($row));
    }

    public function testGetFieldWhitelist()
    {
        $table = $this->getTable();

        if (!$table instanceof TableQueryInterface) {
            $this->markTestSkipped('Table not a query interface');
        }

        $row = $table->get(1, Fields::whitelist(['id', 'title']));

        $expect = array(
            new Record(array(
                'id' => 1,
                'title' => 'foo',
            )),
        );

        $this->assertEquals($expect, array($row));
    }

    public function testGetSupportedFields()
    {
        $table = $this->getTable();

        if (!$table instanceof TableQueryInterface) {
            $this->markTestSkipped('Table not a query interface');
        }

        $fields = $table->getSupportedFields();

        $this->assertEquals(array('id', 'userId', 'title', 'date'), $fields);
    }

    public function testGetCount()
    {
        $table = $this->getTable();

        if (!$table instanceof TableQueryInterface) {
            $this->markTestSkipped('Table not a query interface');
        }

        $this->assertEquals(4, $table->getCount());
        $this->assertEquals(2, $table->getCount(new Condition(array('userId', '=', 1))));
        $this->assertEquals(1, $table->getCount(new Condition(array('userId', '=', 3))));
    }

    public function testNewRecord()
    {
        $table = $this->getTable();

        if (!$table instanceof TableQueryInterface) {
            $this->markTestSkipped('Table not a query interface');
        }

        $obj = $table->newRecord();

        $this->assertInstanceOf(RecordInterface::class, $obj);
    }
}
