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

use PSX\Sql\TableInterface;
use PSX\Sql\TableManager;

/**
 * TableAbstractTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    http://phpsx.org
 */
class TableAbstractTest extends \PHPUnit_Extensions_Database_TestCase
{
    use TableQueryTestTrait;
    use TableManipulationTestTrait;

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
     *
     * @return \PSX\Sql\TableInterface
     */
    protected function getTable()
    {
        return $this->manager->getTable(TestTable::class);
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
        $this->assertEquals('id', $this->getTable()->getPrimaryKey());
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
        $table->delete(['id' => 1]);
        $table->commit();

        $this->assertEquals(3, $table->getCount());
    }

    public function testTransactionRollback()
    {
        $table = $this->getTable();

        $this->assertEquals(4, $table->getCount());

        $table->beginTransaction();
        $table->delete(['id' => 1]);
        $table->rollback();

        $this->assertEquals(4, $table->getCount());
    }
}
