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
use RuntimeException;
use PSX\Sql\Provider\ProviderCollectionInterface;
use PSX\Sql\Provider\ProviderEntityInterface;

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
    public function build($definition, array $context = null, $name = null)
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
        } else {
            if ($context !== null) {
                if (isset($context[$definition])) {
                    return $context[$definition];
                } else {
                    throw new RuntimeException('Referenced unknown key "' . $definition . '" in context');
                }
            } else {
                return $definition;
            }
        }
    }

    protected function getProviderValue(ProviderInterface $provider, array $context = null)
    {
        $data       = $provider->getResult($context);
        $definition = $provider->getDefinition();
        $result     = null;

        if (empty($data)) {
            return null;
        }

        if ($provider instanceof ProviderCollectionInterface) {
            $result = [];
            foreach ($data as $row) {
                if (is_array($row)) {
                    $result[] = $this->build($definition, $row);
                } else {
                    throw new RuntimeException('Collection must contain only array elements');
                }
            }
        } elseif ($provider instanceof ProviderEntityInterface) {
            if (is_array($data)) {
                $result = $this->build($definition, $data);
            } else {
                throw new RuntimeException('Entity must be an array');
            }
        }

        return $result;
    }
}

