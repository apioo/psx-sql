<?php
/*
 * PSX is an open source PHP framework to develop RESTful APIs.
 * For the current version and information visit <https://phpsx.org>
 *
 * Copyright 2010-2023 Christoph Kappestein <christoph.kappestein@gmail.com>
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

use PSX\Sql\Condition;
use PSX\Sql\Fields;
use PSX\Sql\OrderBy;
use PSX\Sql\Tests\Generator\HandlerCommentColumn;
use PSX\Sql\Tests\Generator\HandlerCommentRow;

/**
 * TableQueryTestTrait
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://phpsx.org
 */
trait TableQueryTestTrait
{
    public function testFindAll()
    {
        $table = $this->getTable();
        $result = $table->findAll();

        $this->assertEquals(true, is_array($result));
        $this->assertEquals(4, count($result));

        $expect = [
            HandlerCommentRow::from([
                'id' => 4,
                'userId' => 3,
                'title' => 'blub',
                'date' => new \DateTime('2013-04-29 16:56:32'),
            ]),
            HandlerCommentRow::from([
                'id' => 3,
                'userId' => 2,
                'title' => 'test',
                'date' => new \DateTime('2013-04-29 16:56:32'),
            ]),
            HandlerCommentRow::from([
                'id' => 2,
                'userId' => 1,
                'title' => 'bar',
                'date' => new \DateTime('2013-04-29 16:56:32'),
            ]),
            HandlerCommentRow::from([
                'id' => 1,
                'userId' => 1,
                'title' => 'foo',
                'date' => new \DateTime('2013-04-29 16:56:32'),
            ]),
        ];

        $this->assertEquals($expect, $result);
    }

    public function testFindAllStartIndex()
    {
        $table = $this->getTable();
        $result = $table->findAll(startIndex: 3);

        $this->assertEquals(true, is_array($result));
        $this->assertEquals(1, count($result));

        $expect = [
            HandlerCommentRow::from([
                'id' => 1,
                'userId' => 1,
                'title' => 'foo',
                'date' => new \DateTime('2013-04-29 16:56:32'),
            ]),
        ];

        $this->assertEquals($expect, $result);
    }

    public function testFindAllCount()
    {
        $table = $this->getTable();
        $result = $table->findAll(startIndex: 0, count: 2);

        $this->assertEquals(true, is_array($result));
        $this->assertEquals(2, count($result));

        $expect = [
            HandlerCommentRow::from([
                'id' => 4,
                'userId' => 3,
                'title' => 'blub',
                'date' => new \DateTime('2013-04-29 16:56:32'),
            ]),
            HandlerCommentRow::from([
                'id' => 3,
                'userId' => 2,
                'title' => 'test',
                'date' => new \DateTime('2013-04-29 16:56:32'),
            ]),
        ];

        $this->assertEquals($expect, $result);
    }

    public function testFindAllStartIndexAndCountDefault()
    {
        $table = $this->getTable();
        $result = $table->findAll(startIndex: 2, count: 2);

        $this->assertEquals(true, is_array($result));
        $this->assertEquals(2, count($result));

        $expect = [
            HandlerCommentRow::from([
                'id' => 2,
                'userId' => 1,
                'title' => 'bar',
                'date' => new \DateTime('2013-04-29 16:56:32'),
            ]),
            HandlerCommentRow::from([
                'id' => 1,
                'userId' => 1,
                'title' => 'foo',
                'date' => new \DateTime('2013-04-29 16:56:32'),
            ]),
        ];

        $this->assertEquals($expect, $result);
    }

    public function testFindAllStartIndexAndCountDesc()
    {
        $table = $this->getTable();
        $result = $table->findAll(startIndex: 2, count: 2, sortBy: HandlerCommentColumn::ID, sortOrder: OrderBy::DESC);

        $this->assertEquals(true, is_array($result));
        $this->assertEquals(2, count($result));

        $expect = [
            HandlerCommentRow::from([
                'id' => 2,
                'userId' => 1,
                'title' => 'bar',
                'date' => new \DateTime('2013-04-29 16:56:32'),
            ]),
            HandlerCommentRow::from([
                'id' => 1,
                'userId' => 1,
                'title' => 'foo',
                'date' => new \DateTime('2013-04-29 16:56:32'),
            ]),
        ];

        $this->assertEquals($expect, $result);
    }

    public function testFindAllStartIndexAndCountAsc()
    {
        $table = $this->getTable();
        $result = $table->findAll(startIndex: 2, count: 2, sortBy: HandlerCommentColumn::ID, sortOrder: OrderBy::ASC);

        $this->assertEquals(true, is_array($result));
        $this->assertEquals(2, count($result));

        $expect = [
            HandlerCommentRow::from([
                'id' => 3,
                'userId' => 2,
                'title' => 'test',
                'date' => new \DateTime('2013-04-29 16:56:32'),
            ]),
            HandlerCommentRow::from([
                'id' => 4,
                'userId' => 3,
                'title' => 'blub',
                'date' => new \DateTime('2013-04-29 16:56:32'),
            ]),
        ];

        $this->assertEquals($expect, $result);
    }

    public function testFindAllSortDesc()
    {
        $table = $this->getTable();
        $result = $table->findAll(startIndex: 0, count: 2, sortBy: HandlerCommentColumn::ID, sortOrder: OrderBy::DESC);

        $this->assertEquals(true, is_array($result));
        $this->assertEquals(2, count($result));

        $expect = [
            HandlerCommentRow::from([
                'id' => 4,
                'userId' => 3,
                'title' => 'blub',
                'date' => new \DateTime('2013-04-29 16:56:32'),
            ]),
            HandlerCommentRow::from([
                'id' => 3,
                'userId' => 2,
                'title' => 'test',
                'date' => new \DateTime('2013-04-29 16:56:32'),
            ]),
        ];

        $this->assertEquals($expect, $result);

        foreach ($result as $row) {
            $this->assertTrue($row->getId() != null);
            $this->assertTrue($row->getTitle() != null);
        }

        // check order
        $this->assertEquals(4, $result[0]->getId());
        $this->assertEquals(3, $result[1]->getId());
    }

    public function testFindAllSortAsc()
    {
        $table = $this->getTable();
        $result = $table->findAll(startIndex: 0, count: 2, sortBy: HandlerCommentColumn::ID, sortOrder: OrderBy::ASC);

        $this->assertEquals(true, is_array($result));
        $this->assertEquals(2, count($result));

        $expect = [
            HandlerCommentRow::from([
                'id' => 1,
                'userId' => 1,
                'title' => 'foo',
                'date' => new \DateTime('2013-04-29 16:56:32'),
            ]),
            HandlerCommentRow::from([
                'id' => 2,
                'userId' => 1,
                'title' => 'bar',
                'date' => new \DateTime('2013-04-29 16:56:32'),
            ]),
        ];

        $this->assertEquals($expect, $result);
    }

    public function testFindAllCondition()
    {
        $table = $this->getTable();
        $con = Condition::withAnd()->equals('userId', 1);
        $result = $table->findAll(condition: $con, startIndex: 0, count: 16, sortBy: HandlerCommentColumn::ID, sortOrder: OrderBy::DESC);

        $this->assertEquals(true, is_array($result));
        $this->assertEquals(2, count($result));

        $expect = [
            HandlerCommentRow::from([
                'id' => 2,
                'userId' => 1,
                'title' => 'bar',
                'date' => new \DateTime('2013-04-29 16:56:32'),
            ]),
            HandlerCommentRow::from([
                'id' => 1,
                'userId' => 1,
                'title' => 'foo',
                'date' => new \DateTime('2013-04-29 16:56:32'),
            ]),
        ];

        $this->assertEquals($expect, $result);
    }

    public function testFindAllConditionAndConjunction()
    {
        $table = $this->getTable();

        $con = Condition::withAnd();
        $con->equals('userId', 1);
        $con->equals('userId', 3);
        $result = $table->findAll(condition: $con, startIndex: 0, count: 16, sortBy: HandlerCommentColumn::ID, sortOrder: OrderBy::DESC);

        $this->assertEquals(true, is_array($result));
        $this->assertEquals(0, count($result));

        // check and condition with result
        $con = Condition::withAnd();
        $con->equals('userId', 1);
        $con->equals('title', 'foo');
        $result = $table->findAll(condition: $con, startIndex: 0, count: 16, sortBy: HandlerCommentColumn::ID, sortOrder: OrderBy::DESC);

        $this->assertEquals(true, is_array($result));
        $this->assertEquals(1, count($result));

        $expect = [
            HandlerCommentRow::from([
                'id' => 1,
                'userId' => 1,
                'title' => 'foo',
                'date' => new \DateTime('2013-04-29 16:56:32'),
            ]),
        ];

        $this->assertEquals($expect, $result);
    }

    public function testFindAllConditionOrConjunction()
    {
        $table = $this->getTable();

        $con = Condition::withOr();
        $con->equals('userId', 1);
        $con->equals('userId', 3);
        $result = $table->findAll(condition: $con, startIndex: 0, count: 16, sortBy: HandlerCommentColumn::ID, sortOrder: OrderBy::DESC);

        $this->assertEquals(true, is_array($result));
        $this->assertEquals(3, count($result));

        $expect = [
            HandlerCommentRow::from([
                'id' => 4,
                'userId' => 3,
                'title' => 'blub',
                'date' => new \DateTime('2013-04-29 16:56:32'),
            ]),
            HandlerCommentRow::from([
                'id' => 2,
                'userId' => 1,
                'title' => 'bar',
                'date' => new \DateTime('2013-04-29 16:56:32'),
            ]),
            HandlerCommentRow::from([
                'id' => 1,
                'userId' => 1,
                'title' => 'foo',
                'date' => new \DateTime('2013-04-29 16:56:32'),
            ]),
        ];

        $this->assertEquals($expect, $result);
    }

    public function testFindBy()
    {
        $table = $this->getTable();
        $result = $table->findBy(condition: Condition::withAnd()->equals('userId', 1));

        $this->assertEquals(true, is_array($result));
        $this->assertEquals(2, count($result));

        $expect = [
            HandlerCommentRow::from([
                'id' => 2,
                'userId' => 1,
                'title' => 'bar',
                'date' => new \DateTime('2013-04-29 16:56:32'),
            ]),
            HandlerCommentRow::from([
                'id' => 1,
                'userId' => 1,
                'title' => 'foo',
                'date' => new \DateTime('2013-04-29 16:56:32'),
            ]),
        ];

        $this->assertEquals($expect, $result);
    }

    public function testFindByStartIndexCountOrder()
    {
        $table = $this->getTable();
        $result = $table->findBy(condition: Condition::withAnd()->equals('userId', 1), startIndex: 0, count: 1, sortBy: HandlerCommentColumn::ID, sortOrder: OrderBy::ASC);

        $this->assertEquals(true, is_array($result));
        $this->assertEquals(1, count($result));

        $expect = [
            HandlerCommentRow::from([
                'id' => 1,
                'userId' => 1,
                'title' => 'foo',
                'date' => new \DateTime('2013-04-29 16:56:32'),
            ]),
        ];

        $this->assertEquals($expect, $result);
    }

    public function testFindOneBy()
    {
        $table = $this->getTable();
        $row = $table->findOneBy(condition: Condition::withAnd()->equals('id', 1));

        $expect = [
            HandlerCommentRow::from([
                'id' => 1,
                'userId' => 1,
                'title' => 'foo',
                'date' => new \DateTime('2013-04-29 16:56:32'),
            ]),
        ];

        $this->assertEquals($expect, [$row]);
    }

    public function testFind()
    {
        $table = $this->getTable();
        $row = $table->find(1);

        $expect = [
            HandlerCommentRow::from([
                'id' => 1,
                'userId' => 1,
                'title' => 'foo',
                'date' => new \DateTime('2013-04-29 16:56:32'),
            ]),
        ];

        $this->assertEquals($expect, [$row]);
    }

    public function testGetColumnNames()
    {
        $table = $this->getTable();
        $columnNames = $table->getColumnNames();

        $this->assertEquals(['id', 'userId', 'title', 'date'], $columnNames);
    }

    public function testGetCount()
    {
        $table = $this->getTable();

        $this->assertEquals(4, $table->getCount());
        $this->assertEquals(2, $table->getCount(Condition::withAnd()->equals('userId', 1)));
        $this->assertEquals(1, $table->getCount(Condition::withAnd()->equals('userId', 3)));
    }
}
