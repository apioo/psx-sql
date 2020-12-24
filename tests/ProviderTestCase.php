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

use PHPUnit\Framework\TestCase;
use PSX\Sql\Builder;
use PSX\Sql\Test\DatabaseTestCaseTrait;

/**
 * ProviderTestCase
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    http://phpsx.org
 */
abstract class ProviderTestCase extends TestCase
{
    use DatabaseTestCaseTrait;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpFixture();
    }

    public function getDataSet()
    {
        return include __DIR__ . '/provider_fixture.php';
    }

    public function getConnection()
    {
        return getConnection();
    }

    public function testBuild()
    {
        $builder = new Builder();
        $result  = json_encode($builder->build($this->getDefinition()), JSON_PRETTY_PRINT);

        $expect = <<<JSON
{
    "totalEntries": 2,
    "entries": [
        {
            "id": 1,
            "title": "foo",
            "tags": [
                "foo",
                "bar"
            ],
            "author": {
                "displayName": "Foo Bar",
                "uri": "http:\/\/phpsx.org"
            }
        },
        {
            "id": 2,
            "title": "bar",
            "tags": [
                "foo",
                "bar"
            ],
            "author": {
                "displayName": "Foo Bar",
                "uri": "http:\/\/phpsx.org"
            }
        }
    ]
}
JSON;

        $this->assertJsonStringEqualsJsonString($expect, $result, $result);
    }

    abstract protected function getDefinition();
}