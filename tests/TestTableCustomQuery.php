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

use PSX\Sql\TableAbstract;
use PSX\Sql\TableInterface;

/**
 * TestTableCustomQuery
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    http://phpsx.org
 */
class TestTableCustomQuery extends TableAbstract
{
    public function getName()
    {
        return 'psx_sql_provider_news';
    }

    public function getColumns()
    {
        return array(
            'news.id'                   => TableInterface::TYPE_INT | 10 | TableInterface::PRIMARY_KEY | TableInterface::AUTO_INCREMENT,
            'news.authorId'             => TableInterface::TYPE_INT | 10,
            'news.title'                => TableInterface::TYPE_VARCHAR | 32,
            'news.createDate'           => TableInterface::TYPE_DATETIME,
            'author.name AS authorName' => TableInterface::TYPE_VARCHAR,
            'author.uri AS authorUri'   => TableInterface::TYPE_VARCHAR,
        );
    }

    protected function newQueryBuilder($table)
    {
        return $this->connection->createQueryBuilder()
            ->from($table, 'news')
            ->innerJoin('news', 'psx_sql_provider_author', 'author', 'news.authorId = author.id');
    }
}
