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

namespace PSX\Sql\Provider;

use Doctrine\DBAL\Connection;
use PSX\Sql\Builder;
use PSX\Sql\Exception\BuilderException;
use PSX\Sql\Reference;

/**
 * JsonDefinition
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://phpsx.org
 */
class JsonProvider
{
    private Builder $builder;

    public function __construct(Connection $connection)
    {
        $this->builder = new Builder($connection);
    }

    /**
     * Creates a definition based on a provided JSON payload
     *
     * @throws BuilderException
     */
    public function create(mixed $payload, array $context = []): mixed
    {
        if (!$payload instanceof \stdClass) {
            return null;
        }

        return $this->buildDefinition($payload, $context);
    }

    /**
     * @throws BuilderException
     */
    private function buildDefinition(\stdClass $payload, array $context): mixed
    {
        if (isset($payload->{'$collection'}) && is_string($payload->{'$collection'})) {
            $params = $this->parseParams($payload->{'$params'} ?? null, $context);
            $definition = $this->parseDefinitions($payload->{'$definition'} ?? null, $context);

            return $this->builder->doCollection($payload->{'$collection'}, $params, $definition);
        } elseif (isset($payload->{'$entity'}) && is_string($payload->{'$entity'})) {
            $params = $this->parseParams($payload->{'$params'} ?? null, $context);
            $definition = $this->parseDefinitions($payload->{'$definition'} ?? null, $context);

            return $this->builder->doEntity($payload->{'$entity'}, $params, $definition);
        } elseif (isset($payload->{'$column'}) && is_string($payload->{'$column'})) {
            $params = $this->parseParams($payload->{'$params'} ?? null, $context);
            $definition = $this->parseDefinitions($payload->{'$definition'} ?? null, $context);

            return $this->builder->doColumn($payload->{'$column'}, $params, $definition);
        } elseif (isset($payload->{'$value'}) && is_string($payload->{'$value'})) {
            $params = $this->parseParams($payload->{'$params'} ?? null, $context);
            $definition = $this->parseDefinitions($payload->{'$definition'} ?? null, $context);

            return $this->builder->doValue($payload->{'$value'}, $params, $definition);
        } elseif (isset($payload->{'$field'}) && is_string($payload->{'$field'})) {
            $key = $payload->{'$key'} ?? '';
            switch ($payload->{'$field'}) {
                case 'boolean':
                    return $this->builder->fieldBoolean($key);
                case 'csv':
                    $delimiter = $payload->{'$delimiter'} ?? ',';
                    return $this->builder->fieldCsv($key, $delimiter);
                case 'integer':
                    return $this->builder->fieldInteger($key);
                case 'json':
                    return $this->builder->fieldJson($key);
                case 'number':
                    return $this->builder->fieldNumber($key);
                case 'format':
                    if (!isset($payload->{'$format'})) {
                        throw new BuilderException('Provided format field has no $format value');
                    }
                    return $this->builder->fieldFormat($key, $payload->{'$format'});
                case 'value':
                    return $this->builder->fieldValue($key);
                default:
                    throw new BuilderException('Provided a not valid $field value');
            }
        } else {
            $definition = [];
            foreach ($payload as $key => $value) {
                if ($value instanceof \stdClass) {
                    $definition[$key] = $this->buildDefinition($value, $context);
                } else {
                    $definition[$key] = $value;
                }
            }

            return $definition;
        }
    }

    /**
     * @throws BuilderException
     */
    private function parseDefinitions(mixed $definitions, array $context): mixed
    {
        if ($definitions instanceof \stdClass) {
            return $this->buildDefinition($definitions, $context);
        } else {
            return $definitions;
        }
    }

    private function parseParams(mixed $params, array $context): array
    {
        if (!$params instanceof \stdClass) {
            $params = [];
        }

        $result = [];
        foreach ($params as $key => $value) {
            if ($value instanceof \stdClass) {
                if (isset($value->{'$ref'}) && is_string($value->{'$ref'})) {
                    $result[$key] = new Reference($value->{'$ref'});
                } else {
                    throw new BuilderException('When using an object at a $params value it must contain a $ref key');
                }
            } elseif (is_string($value) && isset($context[$value])) {
                $result[$key] = $context[$value];
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }
}
