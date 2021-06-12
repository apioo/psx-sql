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

use PhpParser\Parser\Parser;
use PhpParser\ParserFactory;
use PSX\Sql\Generator\Generator;

/**
 * GeneratorTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    http://phpsx.org
 */
class GeneratorTest extends TableTestCase
{
    /**
     * @var \PSX\Sql\TableManagerInterface
     */
    protected $manager;

    public function testGenerate()
    {
        $generator = new Generator($this->connection, 'Acme\Foo', 'psx_');

        foreach ($generator->generate() as $className => $source) {
            $file = __DIR__ . '/resource/' . $className . '.php';
            file_put_contents($file, '<?php' . "\n\n" . $source);

            $this->assertFileExists($file);
        }
    }
}
