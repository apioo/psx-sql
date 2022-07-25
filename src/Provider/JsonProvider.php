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
    private Connection $connection;
    private Builder $builder;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
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
    private function buildDefinition(\stdClass $payload, array $context, ?string $propertyName = null): mixed
    {
        if (isset($payload->{'$collection'}) && is_string($payload->{'$collection'})) {
            $params = $this->parseParams($payload->{'$params'} ?? null, $context);
            $key = $payload->{'$key'} ?? null;

            $query = $this->parseLimitQuery($payload->{'$collection'}, $payload, $context);
            $definition = $this->parseDefinitions($payload->{'$definition'} ?? null, $context);

            return $this->builder->doCollection($query, $params, $definition, $key);
        } elseif (isset($payload->{'$entity'}) && is_string($payload->{'$entity'})) {
            $params = $this->parseParams($payload->{'$params'} ?? null, $context);
            $definition = $this->parseDefinitions($payload->{'$definition'} ?? null, $context);

            return $this->builder->doEntity($payload->{'$entity'}, $params, $definition);
        } elseif (isset($payload->{'$column'}) && is_string($payload->{'$column'})) {
            $params = $this->parseParams($payload->{'$params'} ?? null, $context);

            $query = $this->parseLimitQuery($payload->{'$column'}, $payload, $context);
            $definition = $this->parseDefinitions($payload->{'$definition'} ?? null, $context);

            return $this->builder->doColumn($query, $params, $definition);
        } elseif (isset($payload->{'$value'}) && is_string($payload->{'$value'})) {
            $params = $this->parseParams($payload->{'$params'} ?? null, $context);
            $definition = $this->parseDefinitions($payload->{'$definition'} ?? null, $context);

            return $this->builder->doValue($payload->{'$value'}, $params, $definition);
        } elseif (isset($payload->{'$field'}) && is_string($payload->{'$field'})) {
            $key = $payload->{'$key'} ?? $propertyName;
            if ($key === null) {
                throw new BuilderException('Provided no key for field');
            }
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
                    return $key;
            }
        } elseif (isset($payload->{'$context'}) && is_string($payload->{'$context'})) {
            return $context[$payload->{'$context'}] ?? ($payload->{'$default'} ?? null);
        } else {
            $definition = [];
            foreach ($payload as $key => $value) {
                if ($value instanceof \stdClass) {
                    $definition[$key] = $this->buildDefinition($value, $context, $key);
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
                } elseif (isset($value->{'$context'}) && is_string($value->{'$context'})) {
                    $result[$key] = $context[$value->{'$context'}] ?? ($value->{'$default'} ?? null);
                } else {
                    throw new BuilderException('When using an object at a $params value it must contain a $ref key');
                }
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    private function parseLimitQuery(string $query, \stdClass $payload, array $context): string
    {
        $limit = $this->parseInt($payload->{'$limit'} ?? null, $context);
        $offset = $this->parseInt($payload->{'$offset'} ?? null, $context);

        if (!empty($limit)) {
            return $this->connection->getDatabasePlatform()->modifyLimitQuery($query, $limit, $offset);
        } else {
            return $query;
        }
    }

    private function parseInt(mixed $value, array $context): ?int
    {
        if (is_int($value)) {
            return $value;
        } elseif ($value instanceof \stdClass && isset($value->{'$context'}) && is_string($value->{'$context'})) {
            if (isset($context[$value->{'$context'}])) {
                return (int) $context[$value->{'$context'}];
            }

            $default = $value->{'$default'} ?? null;
            if (is_int($default)) {
                return $default;
            }
        }

        return null;
    }
}
