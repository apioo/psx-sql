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

use PSX\Record\Record;
use PSX\Sql\Provider\ProviderCollectionInterface;
use PSX\Sql\Provider\ProviderColumnInterface;
use PSX\Sql\Provider\ProviderEntityInterface;
use PSX\Sql\Provider\ProviderValueInterface;
use RuntimeException;

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
     * Returns an array based on the resolved definition
     *
     * @param mixed $definition
     * @param array $context
     * @return array
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
                if (isset($context[$definition])) {
                    return $context[$definition];
                } else {
                    throw new RuntimeException('Referenced unknown key "' . $definition . '" in context');
                }
            } else {
                return $definition;
            }
        } else {
            return $definition;
        }
    }

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

