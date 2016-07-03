<?php
/*
 * This file is part of the PSX structor package.
 *
 * (c) Christoph Kappestein <christoph.kappestein@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE file 
 * that was distributed with this source code.
 */

namespace PSX\Sql;

use PSX\Record\Record;
use PSX\Record\RecordInterface;
use PSX\Sql\Provider\ProviderCollectionInterface;
use PSX\Sql\Provider\ProviderEntityInterface;
use PSX\Sql\Provider\ProviderValueInterface;
use RuntimeException;

/**
 * The build method resolves the definition through calling every provider and 
 * field objects. The result is an array in the format of the definition
 *
 * @author Christoph Kappestein <christoph.kappestein@gmail.com>
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
        } elseif ($provider instanceof ProviderValueInterface) {
            $result = $data;
        }

        return $result;
    }
}

