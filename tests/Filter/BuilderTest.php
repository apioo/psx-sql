<?php
/*
 * PSX is an open source PHP framework to develop RESTful APIs.
 * For the current version and information visit <https://phpsx.org>
 *
 * Copyright (c) Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace PSX\Sql\Tests\Filter;

use PHPUnit\Framework\Attributes\DataProvider;
use PSX\Sql\Filter\Builder;
use PSX\Sql\TableManager;
use PSX\Sql\Tests\Generator\HandlerCommentColumn;
use PSX\Sql\Tests\Generator\HandlerCommentTable;
use PSX\Sql\Tests\TableTestCase;

/**
 * BuilderTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://phpsx.org
 */
class BuilderTest extends TableTestCase
{
    #[DataProvider('builderProvider')]
    public function testBuilder(string $search, string $expectStatement, array $expectValues): void
    {
        $manager = new TableManager(getConnection());
        $table = $manager->getTable(HandlerCommentTable::class);

        $builder = new Builder();
        $condition = $builder->build($table, HandlerCommentColumn::TITLE, $search);

        $this->assertEquals($expectStatement, $condition->getStatement());
        $this->assertEquals($expectValues, $condition->getValues());
    }

    public static function builderProvider(): array
    {
        return [
            ['userId:octocat', 'WHERE (userId = ?)', ['octocat']],
            ['title:"Bug"', 'WHERE (title LIKE ?)', ['Bug']],
            ['title:"Memory Leak"', 'WHERE (title LIKE ?)', ['Memory Leak']],
            ['octocat', 'WHERE (title LIKE ?)', ['octocat']],
            ['"Bug"', 'WHERE (title LIKE ?)', ['Bug']],
            ['NOT userId:octocat', 'WHERE (NOT (userId = ?))', ['octocat']],
            ['NOT octocat', 'WHERE (NOT (title LIKE ?))', ['octocat']],
            ['NOT NOT userId:octocat', 'WHERE (NOT (NOT (userId = ?)))', ['octocat']],
            ['NOT (userId:octocat)', 'WHERE (NOT (userId = ?))', ['octocat']],
            ['title:"Bug" AND userId:octocat', 'WHERE ((title LIKE ? AND userId = ?))', ['Bug', 'octocat']],
            ['title:"Bug" AND octocat', 'WHERE ((title LIKE ? AND title LIKE ?))', ['Bug', 'octocat']],
            ['id:1 AND userId:2 AND id:3', 'WHERE (((id = ? AND userId = ?) AND id = ?))', ['1', '2', '3']],
            ['title:"Bug" OR title:"Feature"', 'WHERE ((title LIKE ? OR title LIKE ?))', ['Bug', 'Feature']],
            ['octocat OR hubot', 'WHERE ((title LIKE ? OR title LIKE ?))', ['octocat', 'hubot']],
            ['id:1 OR userId:2 OR id:3', 'WHERE (((id = ? OR userId = ?) OR id = ?))', ['1', '2', '3']],
            ['id:1 AND userId:2 OR id:3', 'WHERE (((id = ? AND userId = ?) OR id = ?))', ['1', '2', '3']],
            ['id:1 OR userId:2 AND id:3', 'WHERE ((id = ? OR (userId = ? AND id = ?)))', ['1', '2', '3']],
            ['(id:1 AND userId:2) OR id:3', 'WHERE (((id = ? AND userId = ?) OR id = ?))', ['1', '2', '3']],
            ['((id:1))', 'WHERE (id = ?)', ['1']],
            ['NOT id:1 AND userId:2', 'WHERE ((NOT (id = ?) AND userId = ?))', ['1', '2']],
            ['NOT id:1 OR userId:2', 'WHERE ((NOT (id = ?) OR userId = ?))', ['1', '2']],
            ['NOT (id:1 OR userId:2)', 'WHERE (NOT ((id = ? OR userId = ?)))', ['1', '2']],
            ['(title:"Bug" AND NOT userId:octocat) OR hubot', 'WHERE (((title LIKE ? AND NOT (userId = ?)) OR title LIKE ?))', ['Bug', 'octocat', 'hubot']],
            ['(id:open AND priority:high) OR (userId:octocat AND NOT title:"wontfix")', 'WHERE (((id = ?) OR (userId = ? AND NOT (title LIKE ?))))', ['open', 'octocat', 'wontfix']],
            ['NOT (title:"Bug" AND (userId:octocat OR userId:hubot))', 'WHERE (NOT ((title LIKE ? AND (userId = ? OR userId = ?))))', ['Bug', 'octocat', 'hubot']],
            ['id:1 AND  userId:2', 'WHERE ((id = ? AND userId = ?))', ['1', '2']],
            ['id > 18', 'WHERE (id > ?)', ['18']],
            ['id < 65', 'WHERE (id < ?)', ['65']],
            ['id > 18 AND title:active', 'WHERE ((id > ? AND title LIKE ?))', ['18', 'active']],
            ['NOT id < 100', 'WHERE (NOT (id < ?))', ['100']],
            ['(id > 90 AND title:pro) OR vip', 'WHERE (((id > ? AND title LIKE ?) OR title LIKE ?))', ['90', 'pro', 'vip']],
        ];
    }
}
