<?php
/*
 * PSX is an open source PHP framework to develop RESTful APIs.
 * For the current version and information visit <https://phpsx.org>
 *
 * Copyright 2010-2022 Christoph Kappestein <christoph.kappestein@gmail.com>
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

use DateTime;
use PSX\Record\Record;
use PSX\Record\RecordInterface;
use PSX\Sql\Condition;
use PSX\Sql\Exception\NoFieldsAvailableException;
use PSX\Sql\Exception\NoPrimaryKeyAvailableException;
use PSX\Sql\Table;
use PSX\Sql\TableInterface;
use PSX\Sql\TableManipulationInterface;

/**
 * TableManipulationTestTrait
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://phpsx.org
 */
trait TableManipulationTestTrait
{
    public function testCreate()
    {
        $table = $this->getTable();

        if (!$table instanceof TableManipulationInterface) {
            $this->markTestSkipped('Table not a manipulation interface');
        }

        $record = new Record([
            'id' => 5,
            'userId' => 2,
            'title' => 'foobar',
            'date' => new DateTime(),
        ]);

        $table->create($record);

        $this->assertEquals(5, $table->getLastInsertId());

        $row = $table->find(5);

        $this->assertInstanceOf(RecordInterface::class, $row);
        $this->assertEquals(5, $row->id);
        $this->assertEquals(2, $row->userId);
        $this->assertEquals('foobar', $row->title);
        $this->assertInstanceOf(DateTime::class, $row->date);
    }

    public function testCreateEmpty()
    {
        $this->expectException(NoFieldsAvailableException::class);

        $table = $this->getTable();

        if (!$table instanceof TableManipulationInterface) {
            $this->markTestSkipped('Table not a manipulation interface');
        }

        $table->create(array());
    }

    public function testUpdate()
    {
        $table = $this->getTable();

        if (!$table instanceof TableManipulationInterface) {
            $this->markTestSkipped('Table not a manipulation interface');
        }

        $row = $table->find(1);
        $row->userId = 2;
        $row->title = 'foobar';
        $row->date = new DateTime();

        $table->update($row);

        $row = $table->find(1);

        $this->assertEquals(2, $row->userId);
        $this->assertEquals('foobar', $row->title);
        $this->assertInstanceOf('DateTime', $row->date);
    }

    public function testUpdateEmpty()
    {
        $this->expectException(NoFieldsAvailableException::class);

        $table = $this->getTable();

        if (!$table instanceof TableManipulationInterface) {
            $this->markTestSkipped('Table not a manipulation interface');
        }

        $table->update(array());
    }

    public function testDelete()
    {
        $table = $this->getTable();

        if (!$table instanceof TableManipulationInterface) {
            $this->markTestSkipped('Table not a manipulation interface');
        }

        $row = $table->find(1);

        $table->delete($row);

        $row = $table->find(1);

        $this->assertEmpty($row);
    }

    public function testDeleteEmpty()
    {
        $this->expectException(NoFieldsAvailableException::class);

        $table = $this->getTable();

        if (!$table instanceof TableManipulationInterface) {
            $this->markTestSkipped('Table not a manipulation interface');
        }

        $table->delete(array());
    }

    public function testUpdateNoPrimaryKey()
    {
        $this->expectException(NoPrimaryKeyAvailableException::class);

        $table = new Table($this->manager, 'psx_handler_comment', array('foo' => TableInterface::TYPE_VARCHAR));
        $table->update(array('foo' => 'bar'));
    }

    public function testDeleteNoPrimaryKey()
    {
        $this->expectException(NoPrimaryKeyAvailableException::class);

        $table = new Table($this->manager, 'psx_handler_comment', array('foo' => TableInterface::TYPE_VARCHAR));
        $table->delete(array('foo' => 'bar'));
    }
}
