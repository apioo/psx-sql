<?php

namespace PSX\Sql\Tests\Generator;

enum TableCommandTestColumn : string implements \PSX\Sql\ColumnInterface
{
    case ID = \PSX\Sql\Tests\Generator\TableCommandTestTable::COLUMN_ID;
    case COL_BIGINT = \PSX\Sql\Tests\Generator\TableCommandTestTable::COLUMN_COL_BIGINT;
    case COL_BINARY = \PSX\Sql\Tests\Generator\TableCommandTestTable::COLUMN_COL_BINARY;
    case COL_BLOB = \PSX\Sql\Tests\Generator\TableCommandTestTable::COLUMN_COL_BLOB;
    case COL_BOOLEAN = \PSX\Sql\Tests\Generator\TableCommandTestTable::COLUMN_COL_BOOLEAN;
    case COL_DATETIME = \PSX\Sql\Tests\Generator\TableCommandTestTable::COLUMN_COL_DATETIME;
    case COL_DATETIMETZ = \PSX\Sql\Tests\Generator\TableCommandTestTable::COLUMN_COL_DATETIMETZ;
    case COL_DATE = \PSX\Sql\Tests\Generator\TableCommandTestTable::COLUMN_COL_DATE;
    case COL_DECIMAL = \PSX\Sql\Tests\Generator\TableCommandTestTable::COLUMN_COL_DECIMAL;
    case COL_FLOAT = \PSX\Sql\Tests\Generator\TableCommandTestTable::COLUMN_COL_FLOAT;
    case COL_INTEGER = \PSX\Sql\Tests\Generator\TableCommandTestTable::COLUMN_COL_INTEGER;
    case COL_SMALLINT = \PSX\Sql\Tests\Generator\TableCommandTestTable::COLUMN_COL_SMALLINT;
    case COL_TEXT = \PSX\Sql\Tests\Generator\TableCommandTestTable::COLUMN_COL_TEXT;
    case COL_TIME = \PSX\Sql\Tests\Generator\TableCommandTestTable::COLUMN_COL_TIME;
    case COL_STRING = \PSX\Sql\Tests\Generator\TableCommandTestTable::COLUMN_COL_STRING;
    case COL_JSON = \PSX\Sql\Tests\Generator\TableCommandTestTable::COLUMN_COL_JSON;
    case COL_GUID = \PSX\Sql\Tests\Generator\TableCommandTestTable::COLUMN_COL_GUID;
}