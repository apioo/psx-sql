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

namespace PSX\Sql\Condition;

use Doctrine\DBAL\Platforms\AbstractPlatform;

/**
 * Raw
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://phpsx.org
 */
class Raw extends ExpressionAbstract
{
    protected string $expression;
    protected array $values;

    public function __construct(string $expression, array $values = [])
    {
        parent::__construct('');

        $this->expression = $expression;
        $this->values     = $values;
    }

    public function getExpression(AbstractPlatform $platform): string
    {
        return $this->expression;
    }

    public function getValues(): array
    {
        return $this->values;
    }
}
