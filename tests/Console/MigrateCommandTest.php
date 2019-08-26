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

namespace PSX\Sql\Tests\Console;

use PSX\Sql\Console\MigrateCommand;
use PSX\Sql\TableManager;
use PSX\Sql\Tests\TableTestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * MigrateCommandTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    http://phpsx.org
 */
class MigrateCommandTest extends TableTestCase
{
    public function testCommand()
    {
        $connection = getConnection();

        // save existing table schema
        $schema   = $connection->getSchemaManager()->createSchema();
        $oldTable = $schema->getTable('psx_table_command_test');

        // we remove the table which we migrate
        $connection->query('DROP TABLE psx_table_command_test');

        $manager = new TableManager($connection);
        $command = new MigrateCommand($connection, $manager);

        $commandTester = new CommandTester($command);
        $commandTester->execute(array(
            'table_class' => 'PSX\Sql\Tests\TestTableCommand'
        ));

        $actual = trim($commandTester->getDisplay());

        $this->assertEquals('Migrated table psx_table_command_test successful', $actual);

        // compare the table schema
        $schema   = $connection->getSchemaManager()->createSchema();
        $newTable = $schema->getTable('psx_table_command_test');

        $this->assertEquals($oldTable->getColumns(), $newTable->getColumns());
    }
}
