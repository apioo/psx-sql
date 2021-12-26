<?php
/*
 * PSX is an open source PHP framework to develop RESTful APIs.
 * For the current version and information visit <https://phpsx.org>
 *
 * Copyright 2010-2022 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace PSX\Sql\Field;

use PSX\Sql\FieldInterface;

/**
 * TransformFieldAbstract
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://phpsx.org
 */
abstract class TransformFieldAbstract implements FieldInterface
{
    protected string $field;

    public function __construct(string $field)
    {
        $this->field = $field;
    }

    public function getResult(array|\ArrayObject $context = null): mixed
    {
        if (isset($context[$this->field])) {
            return $this->transform($context[$this->field]);
        } else {
            return null;
        }
    }

    abstract protected function transform(mixed $value): mixed;
}
