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

use PSX\Sql\TableAbstract;
use PSX\Sql\TableInterface;
use PSX\Sql\Provider\DBAL;
use PSX\Sql\Field;

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

        $definition = $this->provider->newCollection($sql, [], [
            'id' => new Field\Integer('id'),
            'title' => new Field\Callback('title', function($title){
                return ucfirst($title);
            }),
            'author' => [
                'id' => new Field\Replace('urn:profile:{userId}'),
                'date' => new Field\DateTime('date'),
            ],
        ]);
        
        return $this->builder->build($definition);
    }
}
