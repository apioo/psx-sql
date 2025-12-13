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

namespace PSX\Sql;

use PSX\Sql\Exception\OperatorException;

/**
 * ComparisonOperator
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://phpsx.org
 */
enum ComparisonOperator
{
    case EQUALS;
    case NOT_EQUALS;
    case LIKE;
    case NOT_LIKE;
    case GREATER;
    case GREATER_THAN;
    case LESS;
    case LESS_THAN;

    public function toSql(): string
    {
        return match ($this) {
            self::EQUALS => '=',
            self::NOT_EQUALS => '!=',
            self::LIKE => 'LIKE',
            self::NOT_LIKE => 'NOT LIKE',
            self::GREATER => '>',
            self::GREATER_THAN => '>=',
            self::LESS => '<',
            self::LESS_THAN => '<=',
        };
    }
}
