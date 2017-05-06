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

namespace PSX\Sql\Tests\Provider;

use PSX\Sql\Field;
use PSX\Sql\Provider\DBAL;
use PSX\Sql\Reference;
use PSX\Sql\Tests\ProviderTestCase;

/**
 * DBALTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    http://phpsx.org
 */
class DBALTest extends ProviderTestCase
{
    protected function getDefinition()
    {
        $provider = new DBAL\Factory($this->connection);

        return [
            'totalEntries' => $provider->newValue('SELECT COUNT(*) AS cnt FROM psx_sql_provider_news', [], new Field\Integer('cnt')),
            'entries' => $provider->newCollection('SELECT id, authorId, title, createDate FROM psx_sql_provider_news ORDER BY id ASC LIMIT :startIndex, 8', ['startIndex' => 0], [
                'id' => new Field\Integer('id'),
                'title' => 'title',
                'tags' => $provider->newColumn('SELECT title FROM psx_sql_provider_news', [], 'title'),
                'author' => $provider->newEntity('SELECT id, name, uri FROM psx_sql_provider_author WHERE id = :id', ['id' => new Reference('authorId')], [
                    'displayName' => 'name',
                    'uri' => 'uri',
                ]),
            ])
        ];
    }
}
