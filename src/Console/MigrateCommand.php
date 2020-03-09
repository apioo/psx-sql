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

namespace PSX\Sql\Console;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use PSX\Sql\TableInterface;
use PSX\Sql\TableManagerInterface;
use PSX\Sql\TypeMapper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * MigrateCommand
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    http://phpsx.org
 */
class MigrateCommand extends Command
{
    /**
     * @var \Doctrine\DBAL\Connection
     */
    protected $connection;

    /**
     * @var \PSX\Sql\TableManagerInterface
     */
    protected $tableManager;

    public function __construct(Connection $connection, TableManagerInterface $tableManager)
    {
        parent::__construct();

        $this->connection   = $connection;
        $this->tableManager = $tableManager;
    }

    protected function configure()
    {
        $this
            ->setName('sql:migrate')
            ->setDescription('Migrates the schema defined in the table class to the database')
            ->addArgument('table_class', InputArgument::REQUIRED, 'Absolute class name of the table');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $table = $this->tableManager->getTable($input->getArgument('table_class'));

        $fromSchema = $this->connection->getSchemaManager()->createSchema();
        $toSchema   = $this->connection->getSchemaManager()->createSchema();

        $this->createTable($toSchema, $table);

        $queries = $fromSchema->getMigrateToSql($toSchema, $this->connection->getDatabasePlatform());
        foreach ($queries as $query) {
            $this->connection->query($query);
        }

        $output->writeln('Migrated table ' . $table->getName() . ' successful');

        return 0;
    }

    protected function createTable(Schema $schema, TableInterface $source)
    {
        $table   = $schema->createTable($source->getName());
        $columns = $source->getColumns();
        $pks     = [];
        
        foreach ($columns as $name => $value) {
            $typeName = TypeMapper::getDoctrineTypeByType($value);
            $options  = $this->getOptionsByValue($value);

            if ($value & TableInterface::PRIMARY_KEY) {
                $pks[] = $name;
            }

            $table->addColumn($name, $typeName, $options);
        }

        if (!empty($pks)) {
            $table->setPrimaryKey($pks);
        }
    }

    protected function getOptionsByValue($value)
    {
        $options = [];
        $length  = $value & 0xFFFFF;

        if ($length > 0) {
            $options['length'] = $length;
        }

        if ($value & TableInterface::AUTO_INCREMENT) {
            $options['autoincrement'] = true;
        }

        if ($value & TableInterface::IS_NULL) {
            $options['notnull'] = false;
        }

        return $options;
    }
}
