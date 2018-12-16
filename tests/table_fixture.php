<?php

return [
    'psx_handler_comment' => [
        ['id' => 1, 'userId' => 1, 'title' => 'foo', 'date' => '2013-04-29 16:56:32'],
        ['id' => 2, 'userId' => 1, 'title' => 'bar', 'date' => '2013-04-29 16:56:32'],
        ['id' => 3, 'userId' => 2, 'title' => 'test', 'date' => '2013-04-29 16:56:32'],
        ['id' => 4, 'userId' => 3, 'title' => 'blub', 'date' => '2013-04-29 16:56:32'],
    ],
    'psx_table_command_test' => [
        [
            'id' => 1,
            'col_bigint' => 68719476735,
            'col_binary' => 'foo',
            'col_blob' => 'foobar',
            'col_boolean' => 1,
            'col_datetime' => '2015-01-21 23:59:59',
            'col_datetimetz' => '2015-01-21 23:59:59',
            'col_date' => '2015-01-21',
            'col_decimal' => 10,
            'col_float' => 10.37,
            'col_integer' => 2147483647,
            'col_smallint' => 255,
            'col_text' => 'foobar',
            'col_time' => '23:59:59',
            'col_string' => 'foobar',
            'col_array' => 'a:1:{s:3:"foo";s:3:"bar";}',
            'col_object' => 'O:8:"stdClass":1:{s:3:"foo";s:3:"bar";}',
            'col_json' => '{"foo":"bar"}',
            'col_guid' => 'ebe865da-4982-4353-bc44-dcdf7239e386'
        ]
    ],
    'psx_sql_table_test' => [
        ['id' => 1, 'title' => 'foo', 'date' => '2013-04-29 16:56:32']
    ],
];
