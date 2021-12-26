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

namespace PSX\Sql\Tests\Generator;

use PSX\DateTime\Date;
use PSX\DateTime\DateTime;
use PSX\Record\Record;
use PSX\Sql\Generator\Generator;
use PSX\Sql\TableManager;
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
    /**
     * @var \PSX\Sql\TableManagerInterface
     */
    protected $manager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->manager = new TableManager($this->connection);
    }

    public function testGenerate()
    {
        $generator = new Generator($this->connection, 'PSX\Sql\Tests\Generator', 'psx_');

        foreach ($generator->generate() as $className => $source) {
            $file = __DIR__ . '/' . $className . '.php';
            file_put_contents($file, '<?php' . "\n\n" . $source);

            $this->assertFileExists($file);
        }

        // test repository
        /** @var TableCommandTestTable $repository */
        $repository = $this->manager->getTable(TableCommandTestTable::class);
        $row = $repository->findOneById(1);

        $this->assertEquals(1, $row->getId());
        $this->assertRecord($row);

        $affected = $repository->create($this->newRecord());
        $row = $repository->findOneById($repository->getLastInsertId());

        $this->assertEquals(1, $affected);
        $this->assertEquals($repository->getLastInsertId(), $row->getId());
        $this->assertRecord($row);
    }

    private function assertRecord(TableCommandTestRow $row)
    {
        $this->assertInstanceOf(TableCommandTestRow::class, $row);
        $this->assertSame(['foo' => 'bar'], $row->getColArray());
        $this->assertSame(68719476735, $row->getColBigint());
        $this->assertSame('foo', stream_get_contents($row->getColBinary()));
        $this->assertSame('foobar', stream_get_contents($row->getColBlob()));
        $this->assertSame(true, $row->getColBoolean());
        $this->assertInstanceOf(\DateTime::class, $row->getColDate());
        $this->assertInstanceOf(\DateTime::class, $row->getColDatetime());
        $this->assertInstanceOf(\DateTime::class, $row->getColDatetimetz());
        $this->assertSame(10.0, $row->getColDecimal());
        $this->assertSame(10.37, $row->getColFloat());
        $this->assertSame('ebe865da-4982-4353-bc44-dcdf7239e386', $row->getColGuid());
        $this->assertSame(2147483647, $row->getColInteger());
        $this->assertEquals((object) ['foo' => 'bar'], $row->getColJson());
        $this->assertEquals((object) ['foo' => 'bar'], $row->getColObject());
        $this->assertSame(255, $row->getColSmallint());
        $this->assertSame('foobar', $row->getColString());
        $this->assertSame('foobar', $row->getColText());
        $this->assertInstanceOf(\DateTime::class, $row->getColTime());
    }

    private function newRecord(): TableCommandTestRow
    {
        $row = new TableCommandTestRow();
        $row->setColArray(['foo' => 'bar']);
        $row->setColBigint(68719476735);
        $row->setColBinary('foo');
        $row->setColBlob('foobar');
        $row->setColBoolean(true);
        $row->setColDate(new \DateTime());
        $row->setColDatetime(new \DateTime());
        $row->setColDatetimetz(new \DateTime());
        $row->setColDecimal(10.0);
        $row->setColFloat(10.37);
        $row->setColGuid('ebe865da-4982-4353-bc44-dcdf7239e386');
        $row->setColInteger(2147483647);
        $row->setColJson((object) ['foo' => 'bar']);
        $row->setColObject((object) ['foo' => 'bar']);
        $row->setColSmallint(255);
        $row->setColString('foobar');
        $row->setColText('foobar');
        $row->setColTime(new \DateTime());

        return $row;
    }
}
