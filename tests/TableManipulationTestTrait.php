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
use PSX\DateTime\LocalDate;
use PSX\DateTime\LocalDateTime;
use PSX\DateTime\Tests\LocalDateTimeTest;
use PSX\Record\RecordInterface;
use PSX\Sql\Exception\NoFieldsAvailableException;
use PSX\Sql\Tests\Generator\HandlerCommentRow;

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

        $record = HandlerCommentRow::fromArray([
            'id' => 5,
            'userId' => 2,
            'title' => 'foobar',
            'date' => new DateTime(),
        ]);

        $table->create($record);

        $this->assertEquals(5, $table->getLastInsertId());

        $row = $table->find(5);

        $this->assertInstanceOf(HandlerCommentRow::class, $row);
        $this->assertEquals(5, $row->getId());
        $this->assertEquals(2, $row->getUserId());
        $this->assertEquals('foobar', $row->getTitle());
        $this->assertInstanceOf(LocalDateTime::class, $row->getDate());
    }

    public function testCreateEmpty()
    {
        $this->expectException(NoFieldsAvailableException::class);

        $table = $this->getTable();
        $table->create(new HandlerCommentRow());
    }

    public function testUpdate()
    {
        $table = $this->getTable();

        $row = $table->find(1);
        $row->setUserId(2);
        $row->setTitle('foobar');
        $row->setDate(LocalDateTime::now());

        $table->update($row);

        $row = $table->find(1);

        $this->assertEquals(2, $row->getUserId());
        $this->assertEquals('foobar', $row->getTitle());
        $this->assertInstanceOf(LocalDateTime::class, $row->getDate());
    }

    public function testUpdateEmpty()
    {
        $this->expectException(NoFieldsAvailableException::class);

        $table = $this->getTable();
        $table->update(new HandlerCommentRow());
    }

    public function testDelete()
    {
        $table = $this->getTable();

        $row = $table->find(1);

        $table->delete($row);

        $row = $table->find(1);

        $this->assertEmpty($row);
    }

    public function testDeleteEmpty()
    {
        $this->expectException(NoFieldsAvailableException::class);

        $table = $this->getTable();
        $table->delete(new HandlerCommentRow());
    }

    public function testUpdateNoPrimaryKey()
    {
        $this->expectException(NoFieldsAvailableException::class);

        $table = $this->getTable();
        $table->update(new HandlerCommentRow(['foo' => 'bar']));
    }

    public function testDeleteNoPrimaryKey()
    {
        $this->expectException(NoFieldsAvailableException::class);

        $table = $this->getTable();
        $table->delete(new HandlerCommentRow(['foo' => 'bar']));
    }
}
