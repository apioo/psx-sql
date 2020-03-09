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
use PSX\Sql\Generator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * GenerateCommand
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    http://phpsx.org
 */
class GenerateCommand extends Command
{
    /**
     * @var \Doctrine\DBAL\Connection
     */
    protected $connection;

    /**
     * @var string
     */
    protected $namespace;

    public function __construct(Connection $connection, $namespace = null)
    {
        parent::__construct();

        $this->connection = $connection;
        $this->namespace  = $namespace;
    }

    protected function configure()
    {
        $this
            ->setName('sql:generate')
            ->setDescription('Generates a representation of a table')
            ->addArgument('table', InputArgument::REQUIRED, 'Name of the database table')
            ->addArgument('format', InputArgument::OPTIONAL, 'Optional the output format possible values are: php, jsonschema');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $table = $this->connection
            ->getSchemaManager()
            ->listTableDetails($input->getArgument('table'));

        switch ($input->getArgument('format')) {
            case 'php':
                $generator = new Generator\Php($this->namespace);
                $response  = $generator->generate($table);
                break;

            default:
            case 'jsonschema':
                $generator = new Generator\JsonSchema();
                $response  = $generator->generate($table);
                break;
        }

        $output->write($response);

        return 0;
    }
}
