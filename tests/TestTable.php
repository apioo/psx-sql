<?php
/*
 * PSX is a open source PHP framework to develop RESTful APIs.
 * For the current version and informations visit <http://phpsx.org>
 *
 * Copyright 2010-2016 Christoph Kappestein <k42b3.x@gmail.com>
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

use PSX\Sql\Field;
use PSX\Sql\Provider\DBAL;
use PSX\Sql\Reference;
use PSX\Sql\TableAbstract;
use PSX\Sql\TableInterface;

/**
 * TestTable
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    http://phpsx.org
 */
class TestTable extends TableAbstract
{
    public function getName()
    {
        return 'psx_handler_comment';
    }

    public function getColumns()
    {
        return array(
            'id'     => TableInterface::TYPE_INT | 10 | TableInterface::PRIMARY_KEY | TableInterface::AUTO_INCREMENT,
            'userId' => TableInterface::TYPE_INT | 10,
            'title'  => TableInterface::TYPE_VARCHAR | 32,
            'date'   => TableInterface::TYPE_DATETIME,
        );
    }

    public function getNestedResult()
    {
        $sql = '  SELECT id,
				         userId,
				         title,
				         date
				    FROM psx_handler_comment
				ORDER BY id DESC';

        $definition = $this->doCollection($sql, [], [
            'id' => $this->type('id', self::TYPE_INT),
            'title' => $this->callback('title', function($title){
                return ucfirst($title);
            }),
            'author' => [
                'id' => $this->replace('urn:profile:{userId}'),
                'date' => $this->dateTime('date'),
            ],
            'note' => $this->doEntity([$this->getTable('PSX\Sql\Tests\TestTableCommand'), 'getOneById'], [new Reference('id')], [
                'comments' => true,
                'title' => 'col_text',
            ])
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
            'id' => $this->type('id', self::TYPE_INT),
            'title' => $this->callback('title', function($title){
                return ucfirst($title);
            }),
            'author' => [
                'id' => $this->replace('urn:profile:{userId}'),
                'date' => $this->dateTime('date'),
            ],
            'note' => $this->doEntity([$this->getTable('PSX\Sql\Tests\TestTableCommand'), 'getOneById'], [new Reference('id')], [
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
            'id' => $this->type('id', self::TYPE_INT),
            'title' => $this->callback('title', function($title){
                return ucfirst($title);
            }),
            'author' => [
                'id' => $this->replace('urn:profile:{userId}'),
                'date' => $this->dateTime('date'),
            ],
            'note' => $this->doEntity([$this->getTable('PSX\Sql\Tests\TestTableCommand'), 'getOneById'], [new Reference('id')], [
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
}
