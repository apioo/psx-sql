<?php

require_once __DIR__ . '/../vendor/autoload.php';

/**
 * @return \Doctrine\DBAL\Connection
 */
function getConnection()
{
    static $connection;

    if ($connection) {
        return $connection;
    }

    switch (getenv('DB')) {
        case 'mysql':
            $params = [
                'dbname'   => 'psx',
                'user'     => 'root',
                'password' => 'test1234',
                'host'     => 'localhost',
                'driver'   => 'pdo_mysql',
            ];

            $params['charset'] = 'utf8';
            $params['driverOptions'] = [
                \PDO::ATTR_EMULATE_PREPARES => false,
            ];
            break;

        default:
        case 'memory':
        case 'sqlite':
            $params = [
                'memory' => true,
                'driver' => 'pdo_sqlite',
            ];
            break;
    }

    $connection = \Doctrine\DBAL\DriverManager::getConnection($params);
    $schema = $connection->createSchemaManager()->introspectSchema();

    $table = $schema->createTable('psx_handler_comment');
    $table->addColumn('id', \Doctrine\DBAL\Types\Types::INTEGER, ['length' => 10, 'autoincrement' => true]);
    $table->addColumn('userId', \Doctrine\DBAL\Types\Types::INTEGER, ['length' => 10]);
    $table->addColumn('title', \Doctrine\DBAL\Types\Types::STRING, ['length' => 32]);
    $table->addColumn('date', \Doctrine\DBAL\Types\Types::DATETIME_MUTABLE);
    $table->setPrimaryKey(['id']);

    $table = $schema->createTable('psx_sql_table_test');
    $table->addColumn('id', \Doctrine\DBAL\Types\Types::INTEGER, ['length' => 10, 'autoincrement' => true]);
    $table->addColumn('title', \Doctrine\DBAL\Types\Types::STRING, ['length' => 32]);
    $table->addColumn('date', \Doctrine\DBAL\Types\Types::DATETIME_MUTABLE);
    $table->setPrimaryKey(['id']);

    $table = $schema->createTable('psx_table_command_test');
    $table->addColumn('id', \Doctrine\DBAL\Types\Types::INTEGER, ['length' => 10, 'autoincrement' => true]);
    $table->addColumn('col_bigint', \Doctrine\DBAL\Types\Types::BIGINT);
    $table->addColumn('col_binary', \Doctrine\DBAL\Types\Types::BINARY);
    $table->addColumn('col_blob', \Doctrine\DBAL\Types\Types::BLOB);
    $table->addColumn('col_boolean', \Doctrine\DBAL\Types\Types::BOOLEAN);
    $table->addColumn('col_datetime', \Doctrine\DBAL\Types\Types::DATETIME_MUTABLE);
    $table->addColumn('col_datetimetz', \Doctrine\DBAL\Types\Types::DATETIMETZ_MUTABLE);
    $table->addColumn('col_date', \Doctrine\DBAL\Types\Types::DATE_MUTABLE);
    $table->addColumn('col_decimal', \Doctrine\DBAL\Types\Types::DECIMAL);
    $table->addColumn('col_float', \Doctrine\DBAL\Types\Types::FLOAT);
    $table->addColumn('col_integer', \Doctrine\DBAL\Types\Types::INTEGER);
    $table->addColumn('col_smallint', \Doctrine\DBAL\Types\Types::SMALLINT);
    $table->addColumn('col_text', \Doctrine\DBAL\Types\Types::TEXT);
    $table->addColumn('col_time', \Doctrine\DBAL\Types\Types::TIME_MUTABLE);
    $table->addColumn('col_string', \Doctrine\DBAL\Types\Types::STRING);
    $table->addColumn('col_json', \Doctrine\DBAL\Types\Types::JSON);
    $table->addColumn('col_guid', \Doctrine\DBAL\Types\Types::GUID);
    $table->setPrimaryKey(['id']);

    $queries = $schema->toSql($connection->getDatabasePlatform());
    foreach ($queries as $query) {
        $connection->executeQuery($query);
    }

    return $connection;
}

