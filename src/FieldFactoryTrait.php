<?php
/*
 * PSX is a open source PHP framework to develop RESTful APIs.
 * For the current version and informations visit <http://phpsx.org>
 *
 * Copyright 2010-2016 Christoph Kappestein <k42b3.x@gmail.com>
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

/**
 * FieldFactoryTrait
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    http://phpsx.org
 */
trait FieldFactoryTrait
{
    protected function fieldBoolean($value)
    {
        return new Field\Boolean($value);
    }

    protected function fieldCallback($key, \Closure $callback)
    {
        return new Field\Callback($key, $callback);
    }

    protected function fieldCsv($key, $delimiter = ',')
    {
        return new Field\Csv($key, $delimiter);
    }

    protected function fieldDateTime($value)
    {
        return new Field\DateTime($value);
    }

    protected function fieldInteger($value)
    {
        return new Field\Integer($value);
    }

    protected function fieldJson($value)
    {
        return new Field\Json($value);
    }

    protected function fieldNumber($value)
    {
        return new Field\Number($value);
    }

    protected function fieldReplace($value)
    {
        return new Field\Replace($value);
    }

    protected function fieldType($value, $type)
    {
        return new Field\Type($value, $this->connection, $type);
    }

    protected function fieldValue($value)
    {
        return new Field\Value($value);
    }
}
