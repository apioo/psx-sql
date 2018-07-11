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

namespace PSX\Sql;

use Doctrine\DBAL\Connection;
use PSX\Record\Record;
use PSX\Sql\Provider\ProviderCollectionInterface;
use PSX\Sql\Provider\ProviderColumnInterface;
use PSX\Sql\Provider\ProviderEntityInterface;
use PSX\Sql\Provider\ProviderValueInterface;
use RuntimeException;
use InvalidArgumentException;

/**
 * The build method resolves the definition through calling every provider and 
 * field objects. The result is an array in the format of the definition
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    http://phpsx.org
 */
class Builder
{
    /**
     * @var \Doctrine\DBAL\Connection
     */
    protected $connection;

    /**
     * @param \Doctrine\DBAL\Connection|null $connection
     */
    public function __construct(Connection $connection = null)
    {
        $this->connection = $connection;
    }

    /**
     * Returns an array based on the resolved definition
     *
     * @param mixed $definition
     * @param array $context
     * @param string $name
     * @return mixed
     */
    public function build($definition, $context = null, $name = null)
    {
        if ($definition instanceof ProviderInterface) {
            return $this->getProviderValue($definition, $context);
        } elseif ($definition instanceof FieldInterface) {
            return $definition->getResult($context);
        } elseif (is_array($definition)) {
            $result = [];
            foreach ($definition as $key => $value) {
                $result[$key] = $this->build($value, $context);
            }

            return new Record(
                $name === null ? 'record' : $name, 
                $result
            );
        } elseif (is_string($definition)) {
            if ($context !== null) {
                if (is_array($context)) {
                    if (array_key_exists($definition, $context)) {
                        return $context[$definition];
                    } else {
                        throw new RuntimeException('Referenced unknown key "' . $definition . '" in context');
                    }
                } elseif ($context instanceof \ArrayAccess) {
                    if ($context->offsetExists($definition)) {
                        return $context->offsetGet($definition);
                    } else {
                        throw new RuntimeException('Referenced unknown key "' . $definition . '" in context');
                    }
                } else {
                    throw new RuntimeException('Context must be either an array or instance of ArrayAccess');
                }
            } else {
                return $definition;
            }
        } else {
            return $definition;
        }
    }

    /**
     * @param mixed $source
     * @param array $arguments
     * @param array $definition
     * @param string|null $key
     * @param \Closure|null $filter
     * @return \PSX\Sql\Provider\ProviderCollectionInterface
     */
    public function doCollection($source, array $arguments, array $definition, $key = null, \Closure $filter = null)
    {
        if (is_callable($source)) {
            return new Provider\Callback\Collection($source, $arguments, $definition, $key, $filter);
        } elseif (is_string($source)) {
            return new Provider\DBAL\Collection($this->connection, $source, $arguments, $definition, $key, $filter);
        } elseif (is_array($source)) {
            return new Provider\Map\Collection($source, $definition, $key, $filter);
        } else {
            throw new InvalidArgumentException('Source must be either a callable, string or array');
        }
    }

    /**
     * @param mixed $source
     * @param array $arguments
     * @param array $definition
     * @return \PSX\Sql\Provider\ProviderEntityInterface
     */
    public function doEntity($source, array $arguments, array $definition)
    {
        if (is_callable($source)) {
            return new Provider\Callback\Entity($source, $arguments, $definition);
        } elseif (is_string($source)) {
            return new Provider\DBAL\Entity($this->connection, $source, $arguments, $definition);
        } elseif (is_array($source)) {
            return new Provider\Map\Entity($source, $definition);
        } else {
            throw new InvalidArgumentException('Source must be either a callable, string or array');
        }
    }

    /**
     * @param mixed $source
     * @param array $arguments
     * @param mixed $definition
     * @return \PSX\Sql\Provider\ProviderColumnInterface
     */
    public function doColumn($source, array $arguments, $definition)
    {
        if (is_callable($source)) {
            return new Provider\Callback\Column($source, $arguments, $definition);
        } elseif (is_string($source)) {
            return new Provider\DBAL\Column($this->connection, $source, $arguments, $definition);
        } elseif (is_array($source)) {
            return new Provider\Map\Column($source, $definition);
        } else {
            throw new InvalidArgumentException('Source must be either a callable, string or array');
        }
    }

    /**
     * @param mixed $source
     * @param array $arguments
     * @param mixed $definition
     * @return \PSX\Sql\Provider\ProviderValueInterface
     */
    public function doValue($source, array $arguments, $definition)
    {
        if (is_callable($source)) {
            return new Provider\Callback\Value($source, $arguments, $definition);
        } elseif (is_string($source)) {
            return new Provider\DBAL\Value($this->connection, $source, $arguments, $definition);
        } elseif (is_array($source)) {
            return new Provider\Map\Value($source, $definition);
        } else {
            throw new InvalidArgumentException('Source must be either a callable, string or array');
        }
    }

    /**
     * @param string $value
     * @return \PSX\Sql\Field\Boolean
     */
    public function fieldBoolean($value)
    {
        return new Field\Boolean($value);
    }

    /**
     * @param string $key
     * @param \Closure $callback
     * @return \PSX\Sql\Field\Callback
     */
    public function fieldCallback($key, \Closure $callback)
    {
        return new Field\Callback($key, $callback);
    }

    /**
     * @param string $key
     * @param string $delimiter
     * @return \PSX\Sql\Field\Csv
     */
    public function fieldCsv($key, $delimiter = ',')
    {
        return new Field\Csv($key, $delimiter);
    }

    /**
     * @param string $value
     * @return \PSX\Sql\Field\DateTime
     */
    public function fieldDateTime($value)
    {
        return new Field\DateTime($value);
    }

    /**
     * @param string $value
     * @return \PSX\Sql\Field\Integer
     */
    public function fieldInteger($value)
    {
        return new Field\Integer($value);
    }

    /**
     * @param string $value
     * @return \PSX\Sql\Field\Json
     */
    public function fieldJson($value)
    {
        return new Field\Json($value);
    }

    /**
     * @param string $value
     * @return \PSX\Sql\Field\Number
     */
    public function fieldNumber($value)
    {
        return new Field\Number($value);
    }

    /**
     * @param string $value
     * @return \PSX\Sql\Field\Replace
     */
    public function fieldReplace($value)
    {
        return new Field\Replace($value);
    }

    /**
     * @param string $value
     * @param integer $type
     * @return \PSX\Sql\Field\Type
     */
    public function fieldType($value, $type)
    {
        return new Field\Type($value, $this->connection, $type);
    }

    /**
     * @param string $value
     * @return \PSX\Sql\Field\Value
     */
    public function fieldValue($value)
    {
        return new Field\Value($value);
    }

    /**
     * @param \PSX\Sql\ProviderInterface $provider
     * @param array|\ArrayAccess|null $context
     * @return array|mixed|null
     */
    protected function getProviderValue(ProviderInterface $provider, $context = null)
    {
        $data       = $provider->getResult($context);
        $definition = $provider->getDefinition();
        $result     = null;

        if (empty($data)) {
            return null;
        }

        if ($provider instanceof ProviderCollectionInterface) {
            $result = [];
            $key    = $provider->getKey();
            $filter = $provider->getFilter();

            if ($key === null) {
                foreach ($data as $row) {
                    $result[] = $this->build($definition, $row);
                }
            } elseif (is_string($key)) {
                foreach ($data as $row) {
                    $result[$row[$key]] = $this->build($definition, $row);
                }
            } elseif (is_callable($key)) {
                foreach ($data as $row) {
                    $return = call_user_func_array($key, [$row]);
                    $result[$return] = $this->build($definition, $row);
                }
            }

            if ($filter !== null) {
                $result = call_user_func_array($filter, [$result]);
            }
        } elseif ($provider instanceof ProviderEntityInterface) {
            $result = $this->build($definition, $data);
        } elseif ($provider instanceof ProviderColumnInterface) {
            $result = [];
            foreach ($data as $row) {
                $result[] = $this->build($definition, $row);
            }
        } elseif ($provider instanceof ProviderValueInterface) {
            $result = $this->build($definition, $data);
        }

        return $result;
    }
}

