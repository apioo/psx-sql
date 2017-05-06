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

use PSX\Sql\Reference;
use PSX\Sql\TableInterface;
use PSX\Sql\ViewAbstract;

/**
 * TestView
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    http://phpsx.org
 */
class TestView extends ViewAbstract
{
    public function getNestedResult()
    {
        $sql = '  SELECT id,
                         userId,
                         title,
                         date
                    FROM psx_handler_comment
                ORDER BY id DESC';

        $definition = $this->doCollection($sql, [], [
            'id' => $this->fieldType('id', TableInterface::TYPE_INT),
            'title' => $this->fieldCallback('title', function($title){
                return ucfirst($title);
            }),
            'author' => [
                'id' => $this->fieldReplace('urn:profile:{userId}'),
                'date' => $this->fieldDateTime('date'),
            ],
            'note' => $this->doEntity([$this->getTable(TestTableCommand::class), 'getOneById'], [new Reference('id')], [
                'comments' => true,
                'title' => 'col_text',
            ]),
            'count' => $this->doValue('SELECT COUNT(*) AS cnt FROM psx_handler_comment', [], $this->fieldInteger('cnt')),
            'tags' => $this->doColumn('SELECT date FROM psx_handler_comment', [], 'date'),
        ]);

        return $this->build($definition);
    }

    public function getNestedResultKey()
    {
        $sql = '  SELECT id,
                         userId,
                         title,
                         date
                    FROM psx_handler_comment
                ORDER BY id DESC';

        $definition = $this->doCollection($sql, [], [
            'id' => $this->fieldType('id', TableInterface::TYPE_INT),
            'title' => $this->fieldCallback('title', function($title){
                return ucfirst($title);
            }),
            'author' => [
                'id' => $this->fieldReplace('urn:profile:{userId}'),
                'date' => $this->fieldDateTime('date'),
            ],
            'note' => $this->doEntity([$this->getTable(TestTableCommand::class), 'getOneById'], [new Reference('id')], [
                'comments' => true,
                'title' => 'col_text',
            ])
        ], function($row){
            return substr(md5($row['userId']), 0, 8);
        });

        return $this->build($definition);
    }

    public function getNestedResultFilter()
    {
        $sql = '  SELECT id,
                         userId,
                         title,
                         date
                    FROM psx_handler_comment
                ORDER BY id DESC';

        $definition = $this->doCollection($sql, [], [
            'id' => $this->fieldType('id', TableInterface::TYPE_INT),
            'title' => $this->fieldCallback('title', function($title){
                return ucfirst($title);
            }),
            'author' => [
                'id' => $this->fieldReplace('urn:profile:{userId}'),
                'date' => $this->fieldDateTime('date'),
            ],
            'note' => $this->doEntity([$this->getTable(TestTableCommand::class), 'getOneById'], [new Reference('id')], [
                'comments' => true,
                'title' => 'col_text',
            ])
        ], null, function(array $result){
            return array_values(array_filter($result, function($row){
                return $row['author']['id'] == 'urn:profile:1';
            }));
        });

        return $this->build($definition);
    }

    public function getNestedResultFields()
    {
        $data = [
            'boolean' => '1',
            'callback' => 'foo',
            'csv' => 'foo,bar',
            'dateTime' => '2017-03-05 00:00:00',
            'integer' => '1',
            'json' => '{"foo": "bar"}',
            'number' => '12.34',
            'replace' => 'foo',
            'type' => '1',
            'value' => 'foo',
        ];

        $definition = $this->doEntity($data, [], [
            'boolean' => $this->fieldBoolean('boolean'),
            'callback' => $this->fieldCallback('callback', function(){
                return 'bar';
            }),
            'csv' => $this->fieldCsv('csv'),
            'dateTime' => $this->fieldDateTime('dateTime'),
            'integer' => $this->fieldInteger('integer'),
            'json' => $this->fieldJson('json'),
            'number' => $this->fieldNumber('number'),
            'replace' => $this->fieldReplace('http://foo.com/{replace}'),
            'type' => $this->fieldType('type', TableInterface::TYPE_INT),
            'value' => $this->fieldValue('bar'),
        ]);

        return $this->build($definition);
    }
}
