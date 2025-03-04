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

use PSX\Sql\TableInterface;
use PSX\Sql\TableManager;
use PSX\Sql\TableManagerInterface;
use PSX\Sql\Tests\Generator\HandlerCommentRow;
use PSX\Sql\Tests\Generator\HandlerCommentTable;

/**
 * TableAbstractTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://phpsx.org
 */
class TableAbstractTest extends TableTestCase
{
    use TableQueryTestTrait;
    use TableManipulationTestTrait;

    protected TableManagerInterface $manager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->manager = new TableManager($this->connection);
    }

    /**
     * Returns the table which should be used for the test. The table must
     * have the following fields: id, userId, title, date. And the following
     * default values:
     * <code>
     *  id = 1,
     *  userId = 1,
     *  title = 'foo',
     *  date = '2013-04-29 16:56:32'
     *
     *  id = 2,
     *  userId = 1,
     *  title = 'bar',
     *  date = '2013-04-29 16:56:32'
     *
     *  id = 3,
     *  userId = 2,
     *  title = 'test',
     *  date = '2013-04-29 16:56:32'
     *
     *  id = 4,
     *  userId = 3,
     *  title = 'blub',
     *  date = '2013-04-29 16:56:32'
     * </code>
     */
    protected function getTable(): HandlerCommentTable
    {
        return $this->manager->getTable(HandlerCommentTable::class);
    }

    public function testGetName()
    {
        $this->assertEquals('psx_handler_comment', $this->getTable()->getName());
    }

    public function testGetColumns()
    {
        $expect = array(
            'id'     => TableInterface::TYPE_INT | 10 | TableInterface::PRIMARY_KEY | TableInterface::AUTO_INCREMENT,
            'userId' => TableInterface::TYPE_INT | 10,
            'title'  => TableInterface::TYPE_VARCHAR | 32,
            'date'   => TableInterface::TYPE_DATETIME,
        );

        $this->assertEquals($expect, $this->getTable()->getColumns());
    }

    public function testGetDisplayName()
    {
        $this->assertEquals('comment', $this->getTable()->getDisplayName());
    }

    public function testGetPrimaryKey()
    {
        $this->assertEquals('id', $this->getTable()->getPrimaryKeys()[0]);
    }

    public function testHasColumn()
    {
        $this->assertTrue($this->getTable()->hasColumn('title'));
        $this->assertFalse($this->getTable()->hasColumn('foobar'));
    }

    public function testTransaction()
    {
        $table = $this->getTable();

        $this->assertEquals(4, $table->getCount());
        
        $table->beginTransaction();
        $table->deleteById(1);
        $table->commit();

        $this->assertEquals(3, $table->getCount());
    }

    public function testTransactionRollback()
    {
        $table = $this->getTable();

        $this->assertEquals(4, $table->getCount());

        $table->beginTransaction();
        $table->deleteById(1);
        $table->rollback();

        $this->assertEquals(4, $table->getCount());
    }
}
