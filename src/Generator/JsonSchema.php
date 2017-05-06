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

namespace PSX\Sql\Generator;

use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Table;
use PSX\Sql\GeneratorInterface;

/**
 * JsonSchema
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    http://phpsx.org
 */
class JsonSchema implements GeneratorInterface
{
    public function generate(Table $table)
    {
        $schema = (object) [
            '$schema'    => 'http://json-schema.org/draft-04/schema#',
            'title'      => $table->getName(),
            'type'       => 'object',
            'properties' => new \stdClass(),
        ];

        foreach ($table->getColumns() as $column) {
            $schema->properties->{$column->getName()} = $this->getPropertyForColumn($column);
        }

        return json_encode($schema, JSON_PRETTY_PRINT);
    }

    protected function getPropertyForColumn(Column $column)
    {
        $typeName = $column->getType()->getName();

        $property = new \stdClass();
        $property->type = $this->getType($typeName);

        $format = $this->getFormat($typeName);
        if ($format !== null) {
            $property->format = $format;
        }

        if ($column->getLength() > 0) {
            if ($property->type === 'string') {
                $property->maxLength = $column->getLength();
            } elseif ($property->type === 'number' || $property->type === 'integer') {
                $property->maximum = $column->getLength();
            }
        }

        $comment = $column->getComment();
        if (!empty($comment)) {
            $property->description = $comment;
        }

        return $property;
    }

    protected function getType($typeName)
    {
        switch ($typeName) {
            case 'boolean':
                return 'boolean';

            case 'bigint':
            case 'integer':
            case 'smallint':
                return 'integer';
                break;

            case 'decimal':
            case 'float':
                return 'number';
                break;

            default:
                return 'string';
        }
    }

    protected function getFormat($typeName)
    {
        switch ($typeName) {
            case 'datetime':
            case 'datetimetz':
                return 'date-time';
                break;

            case 'date':
                return 'date';
                break;

            case 'time':
                return 'time';
                break;

            default:
                return null;
        }
    }
}
