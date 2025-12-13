<?php

namespace PSX\Sql\Tests\Generator;

enum HandlerCommentColumn : string implements \PSX\Sql\ColumnInterface
{
    case ID = \PSX\Sql\Tests\Generator\HandlerCommentTable::COLUMN_ID;
    case USERID = \PSX\Sql\Tests\Generator\HandlerCommentTable::COLUMN_USERID;
    case TITLE = \PSX\Sql\Tests\Generator\HandlerCommentTable::COLUMN_TITLE;
    case DATE = \PSX\Sql\Tests\Generator\HandlerCommentTable::COLUMN_DATE;
}