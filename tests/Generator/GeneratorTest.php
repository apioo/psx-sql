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

namespace PSX\Sql\Tests\Generator;

use PSX\DateTime\Date;
use PSX\DateTime\DateTime;
use PSX\DateTime\LocalDate;
use PSX\DateTime\LocalDateTime;
use PSX\DateTime\LocalTime;
use PSX\Sql\Generator\Generator;
use PSX\Sql\TableManager;
use PSX\Sql\TableManagerInterface;
use PSX\Sql\Tests\TableTestCase;

/**
 * GeneratorTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://phpsx.org
 */
class GeneratorTest extends TableTestCase
{
    private TableManagerInterface $manager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->manager = new TableManager($this->connection);
        $this->generate();
    }

    private function generate()
    {
        $generator = new Generator($this->connection, 'PSX\Sql\Tests\Generator', 'psx_');

        foreach ($generator->generate() as $className => $source) {
            $file = __DIR__ . '/' . $className . '.php';
            file_put_contents($file, '<?php' . "\n\n" . $source);
        }
    }

    public function testFindOneBy()
    {
        $row = $this->getTable()->findOneById(1);

        $this->assertEquals(1, $row->getId());
        $this->assertRecord($row);
    }

    public function testCreate()
    {
        $affected = $this->getTable()->create($this->newRecord());
        $row = $this->getTable()->findOneById($this->getTable()->getLastInsertId());

        $this->assertEquals(1, $affected);
        $this->assertEquals($this->getTable()->getLastInsertId(), $row->getId());
        $this->assertRecord($row);
    }

    public function testUpdateBy()
    {
        $row = new TableCommandTestRow();
        $row->setColString('foobaz');
        $return = $this->getTable()->updateByColGuid('ebe865da-4982-4353-bc44-dcdf7239e386', $row);
        $this->assertEquals(1, $return);

        $row = $this->getTable()->findOneByColString('foobaz');
        $this->assertNotNull($row);
        $this->assertEquals('foobaz', $row->getColString());
    }

    public function testDeleteBy()
    {
        $return = $this->getTable()->deleteByColGuid('ebe865da-4982-4353-bc44-dcdf7239e386');
        $this->assertEquals(1, $return);

        $row = $this->getTable()->findOneByColGuid('ebe865da-4982-4353-bc44-dcdf7239e386');
        $this->assertNull($row);
    }

    private function assertRecord(TableCommandTestRow $row)
    {
        $this->assertInstanceOf(TableCommandTestRow::class, $row);
        $this->assertSame('68719476735', $row->getColBigint());
        $this->assertSame('foo', stream_get_contents($row->getColBinary()));
        $this->assertSame('foobar', stream_get_contents($row->getColBlob()));
        $this->assertSame(true, $row->getColBoolean());
        $this->assertInstanceOf(LocalDate::class, $row->getColDate());
        $this->assertInstanceOf(LocalDateTime::class, $row->getColDatetime());
        $this->assertInstanceOf(LocalDateTime::class, $row->getColDatetimetz());
        $this->assertSame('10', $row->getColDecimal());
        $this->assertSame(10.37, $row->getColFloat());
        $this->assertSame('ebe865da-4982-4353-bc44-dcdf7239e386', $row->getColGuid());
        $this->assertSame(2147483647, $row->getColInteger());
        $this->assertEquals((object) ['foo' => 'bar'], $row->getColJson());
        $this->assertSame(255, $row->getColSmallint());
        $this->assertSame('foobar', $row->getColString());
        $this->assertSame('foobar', $row->getColText());
        $this->assertInstanceOf(LocalTime::class, $row->getColTime());
    }

    private function newRecord(): TableCommandTestRow
    {
        $row = new TableCommandTestRow();
        $row->setColBigint(68719476735);
        $row->setColBinary('foo');
        $row->setColBlob('foobar');
        $row->setColBoolean(true);
        $row->setColDate(LocalDate::now());
        $row->setColDatetime(LocalDateTime::now());
        $row->setColDatetimetz(LocalDateTime::now());
        $row->setColDecimal(10.0);
        $row->setColFloat(10.37);
        $row->setColGuid('ebe865da-4982-4353-bc44-dcdf7239e386');
        $row->setColInteger(2147483647);
        $row->setColJson((object) ['foo' => 'bar']);
        $row->setColSmallint(255);
        $row->setColString('foobar');
        $row->setColText('foobar');
        $row->setColTime(LocalTime::now());

        return $row;
    }

    private function getTable(): TableCommandTestTable
    {
        return $this->manager->getTable(TableCommandTestTable::class);
    }
}
