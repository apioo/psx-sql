<?php

namespace PSX\Sql\Tests\Generator;

enum SqlTableTestColumn : string implements \PSX\Sql\ColumnInterface
{
    case ID = \PSX\Sql\Tests\Generator\SqlTableTestTable::COLUMN_ID;
    case TITLE = \PSX\Sql\Tests\Generator\SqlTableTestTable::COLUMN_TITLE;
    case DATE = \PSX\Sql\Tests\Generator\SqlTableTestTable::COLUMN_DATE;
}