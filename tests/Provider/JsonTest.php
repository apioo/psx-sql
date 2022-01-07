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

namespace PSX\Sql\Tests\Provider;

use PSX\Sql\Provider\JsonProvider;
use PSX\Sql\Tests\ProviderTestCase;

/**
 * JsonTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://phpsx.org
 */
class JsonTest extends ProviderTestCase
{
    protected function getDefinition()
    {
        $provider = new JsonProvider($this->connection);

        $json = <<<'JSON'
{
  "totalEntries": {
    "$value": "SELECT COUNT(*) AS cnt FROM psx_sql_provider_news",
    "$definition": {
      "$key": "cnt",
      "$field": "integer"
    }
  },
  "entries": {
    "$collection": "SELECT id, authorId, title, createDate FROM psx_sql_provider_news ORDER BY id ASC LIMIT :startIndex, 8",
    "$params": {
      "startIndex": 0
    },
    "$definition": {
      "id": {
        "$key": "id",
        "$field": "integer"
      },
      "title": "title",
      "tags": {
        "$column": "SELECT title FROM psx_sql_provider_news",
        "$definition": "title"
      },
      "author": {
        "$entity": "SELECT id, name, uri FROM psx_sql_provider_author WHERE id = :id",
        "$params": {
          "id": {
            "$ref": "authorId"
          }
        },
        "$definition": {
          "displayName": "name",
          "uri": "uri"
        }
      }
    }
  }
}
JSON;

        return $provider->create(\json_decode($json));
    }
}
