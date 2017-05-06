<?php
/*
 * PSX is a open source PHP framework to develop RESTful APIs.
 * For the current version and informations visit <http://phpsx.org>
 *
 * Copyright 2010-2017 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace PSX\Sql\Condition;

use Doctrine\DBAL\Platforms\AbstractPlatform;

/**
 * Basic
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    http://phpsx.org
 */
class Basic extends ExpressionAbstract
{
    protected $operator;
    protected $value;

    public function __construct($column, $operator, $value, $conjunction = 'AND')
    {
        parent::__construct($column, $conjunction);

        $this->operator = $operator;
        $this->value    = $value;
    }

    public function getExpression(AbstractPlatform $platform)
    {
        return $this->column . ' ' . $this->operator . ' ?';
    }

    public function getValues()
    {
        return [$this->value];
    }
}
