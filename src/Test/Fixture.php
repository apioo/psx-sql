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

namespace PSX\Sql\Test;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms;

/**
 * Fixture
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://phpsx.org
 */
class Fixture
{
    /**
     * @param \Doctrine\DBAL\Connection $connection
     */
    public static function truncate(Connection $connection)
    {
        $tables   = $connection->getSchemaManager()->listTableNames();
        $platform = $connection->getDatabasePlatform();

        if ($platform instanceof Platforms\MySqlPlatform) {
            $connection->executeQuery('SET FOREIGN_KEY_CHECKS = 0');

            foreach ($tables as $table) {
                $connection->executeQuery('TRUNCATE ' . $table);
            }

            $connection->executeQuery('SET FOREIGN_KEY_CHECKS = 1');
        } elseif ($platform instanceof Platforms\PostgreSqlPlatform) {
            foreach ($tables as $table) {
                $connection->executeQuery('TRUNCATE ' . $table . ' RESTART IDENTITY CASCADE');
            }
        } elseif ($platform instanceof Platforms\SqlitePlatform) {
            foreach ($tables as $table) {
                $connection->executeQuery('DELETE FROM ' . $table . ' WHERE 1=1');
                $connection->executeQuery('DELETE FROM sqlite_sequence WHERE name="' . $table . '"');
            }
        } else {
            // for all other platforms we simply try to delete all data using
            // standard SQL ignoring potential foreign key problems
            foreach ($tables as $table) {
                $connection->executeQuery('DELETE FROM ' . $table . ' WHERE 1=1');
            }
        }
    }

    /**
     * @param \Doctrine\DBAL\Connection $connection
     * @param array|\ArrayObject $data
     */
    public static function insert(Connection $connection, $data)
    {
        foreach ($data as $tableName => $rows) {
            foreach ($rows as $row) {
                $connection->insert($tableName, $row);
            }
        }
    }
}
