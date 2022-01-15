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

use PHPUnit\Framework\TestCase;
use PSX\Sql\Builder;
use PSX\Sql\Field;
use PSX\Sql\Provider\Map;

/**
 * FieldTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://phpsx.org
 */
class FieldTest extends TableTestCase
{
    public function testFields()
    {
        $data = [
            'boolean' => '1',
            'callback' => 'foo',
            'dateTime' => '2016-03-01 00:00:00',
            'number' => '1.4',
            'integer' => '2',
        ];

        $definition = [
            'fields' => new Map\Entity($data, [
                'boolean' => new Field\Boolean('boolean'),
                'callback' => new Field\Callback('callback', function($value){
                    return ucfirst($value);
                }),
                'dateTime' => new Field\DateTime('dateTime'),
                'number' => new Field\Number('number'),
                'integer' => new Field\Integer('integer'),
                'replace' => new Field\Format('integer', 'http://foobar.com/entry/%s'),
                'value' => new Field\Value('bar'),
            ]),
        ];

        $builder = new Builder($this->connection);
        $result  = json_encode($builder->build($definition), JSON_PRETTY_PRINT);

        $expect = <<<JSON
{
    "fields": {
        "boolean": true,
        "callback": "Foo",
        "dateTime": "2016-03-01T00:00:00Z",
        "number": 1.4,
        "integer": 2,
        "replace": "http:\/\/foobar.com\/entry\/2",
        "value": "bar"
    }
}
JSON;

        $this->assertJsonStringEqualsJsonString($expect, $result, $result);
    }
}