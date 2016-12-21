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

/**
 * ProviderInterface
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    http://phpsx.org
 */
interface ProviderInterface
{
    /**
     * Returns the actual result. Is either an array or ArrayAccess object
     *
     * @param array|\ArrayAccess $context
     * @return array
     */
    public function getResult($context = null);

    /**
     * Returns the definition of the result
     *
     * @return array
     */
    public function getDefinition();
}
