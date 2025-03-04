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

namespace PSX\Sql\Test;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception\ConstraintViolationException;
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
    public static function truncate(Connection $connection): void
    {
        $tables   = $connection->createSchemaManager()->listTableNames();
        $platform = $connection->getDatabasePlatform();

        if ($platform instanceof Platforms\MySQLPlatform) {
            $connection->executeStatement('SET FOREIGN_KEY_CHECKS = 0');

            foreach ($tables as $table) {
                $connection->executeStatement('TRUNCATE ' . $table);
            }

            $connection->executeStatement('SET FOREIGN_KEY_CHECKS = 1');
        } elseif ($platform instanceof Platforms\PostgreSQLPlatform) {
            $connection->executeStatement('SET session_replication_role = \'replica\'');

            foreach ($tables as $table) {
                $connection->executeStatement('TRUNCATE ' . $table . ' RESTART IDENTITY CASCADE');
            }

            $connection->executeStatement('SET session_replication_role = \'origin\'');
        } elseif ($platform instanceof Platforms\SqlitePlatform) {
            foreach ($tables as $table) {
                $connection->executeStatement('DELETE FROM ' . $table . ' WHERE 1=1');
                $connection->executeStatement('DELETE FROM sqlite_sequence WHERE name="' . $table . '"');
            }
        } else {
            // for all other platforms we simply try to delete all data using
            // standard SQL ignoring potential foreign key problems
            foreach ($tables as $table) {
                $connection->executeStatement('DELETE FROM ' . $table . ' WHERE 1=1');
            }
        }
    }

    public static function insert(Connection $connection, array $data): void
    {
        foreach ($data as $tableName => $rows) {
            foreach ($rows as $row) {
                foreach ($row as $key => $value) {
                    if ($value instanceof ResolvableInterface) {
                        $row[$key] = $value->resolve($connection);
                    }
                }

                try {
                    $connection->insert($tableName, $row);
                } catch (ConstraintViolationException $e) {
                }
            }
        }
    }
}
