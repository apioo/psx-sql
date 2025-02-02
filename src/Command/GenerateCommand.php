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

namespace PSX\Sql\Command;

use Doctrine\DBAL\Connection;
use PSX\Sql\Generator\Generator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * GenerateCommand
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://phpsx.org
 */
class GenerateCommand extends Command
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        parent::__construct();

        $this->connection = $connection;
    }

    protected function configure(): void
    {
        $this
            ->setName('sql:generate')
            ->setDescription('Generates all models and repositories based on the available database schema')
            ->addArgument('target', InputArgument::OPTIONAL, 'The output directory otherwise we use the CWD')
            ->addOption('namespace', 's', InputOption::VALUE_REQUIRED, 'Optional the namespace which should be used')
            ->addOption('prefix', 'p', InputOption::VALUE_REQUIRED, 'Optional removes the provided prefix from the table name');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $target = (string) ($input->getArgument('target') ?: getcwd());
        if (!is_dir($target)) {
            throw new \RuntimeException('Target is not a directory');
        }

        $generator = new Generator($this->connection, $input->getOption('namespace'));
        $count = 0;
        foreach ($generator->generate() as $className => $source) {
            file_put_contents($target . '/' . $className . '.php', '<?php' . "\n\n" . $source);
            $count++;
        }

        $output->writeln('Generated ' . $count . ' files at ' . $target);

        return 0;
    }
}
