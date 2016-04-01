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

    $params = array(
        'driver' => 'pdo_sqlite',
        'memory' => true
    );

    $connection = \Doctrine\DBAL\DriverManager::getConnection($params);
    $fromSchema = $connection->getSchemaManager()->createSchema();

    $toSchema = new \Doctrine\DBAL\Schema\Schema();
    $table = $toSchema->createTable('psx_handler_comment');
    $table->addColumn('id', 'integer', array('length' => 10, 'autoincrement' => true));
    $table->addColumn('userId', 'integer', array('length' => 10));
    $table->addColumn('title', 'string', array('length' => 32));
    $table->addColumn('date', 'datetime');
    $table->setPrimaryKey(array('id'));

    $table = $toSchema->createTable('psx_sql_table_test');
    $table->addColumn('id', 'integer', array('length' => 10, 'autoincrement' => true));
    $table->addColumn('title', 'string', array('length' => 32));
    $table->addColumn('date', 'datetime');
    $table->setPrimaryKey(array('id'));

    $table = $toSchema->createTable('psx_table_command_test');
    $table->addColumn('id', 'integer', array('length' => 10, 'autoincrement' => true));
    $table->addColumn('col_bigint', 'bigint');
    $table->addColumn('col_blob', 'blob');
    $table->addColumn('col_boolean', 'boolean');
    $table->addColumn('col_datetime', 'datetime');
    $table->addColumn('col_datetimetz', 'datetimetz');
    $table->addColumn('col_date', 'date');
    $table->addColumn('col_decimal', 'decimal');
    $table->addColumn('col_float', 'float');
    $table->addColumn('col_integer', 'integer');
    $table->addColumn('col_smallint', 'smallint');
    $table->addColumn('col_text', 'text');
    $table->addColumn('col_time', 'time');
    $table->addColumn('col_string', 'string');
    $table->addColumn('col_array', 'text', array('notnull' => false));
    $table->addColumn('col_object', 'text', array('notnull' => false));
    $table->setPrimaryKey(array('id'));

    $table = $toSchema->createTable('psx_sql_provider_news');
    $table->addColumn('id', 'integer', array('length' => 10, 'autoincrement' => true));
    $table->addColumn('authorId', 'integer', array('length' => 10));
    $table->addColumn('title', 'string', array('length' => 32));
    $table->addColumn('createDate', 'datetime');
    $table->setPrimaryKey(array('id'));

    $table = $toSchema->createTable('psx_sql_provider_author');
    $table->addColumn('id', 'integer', array('length' => 10, 'autoincrement' => true));
    $table->addColumn('name', 'string', array('length' => 64));
    $table->addColumn('uri', 'string', array('length' => 64));
    $table->setPrimaryKey(array('id'));

    $queries = $fromSchema->getMigrateToSql($toSchema, $connection->getDatabasePlatform());
    foreach ($queries as $query) {
        $connection->query($query);
    }

    return $connection;
}

