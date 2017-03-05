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
 * FieldInterface
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    http://phpsx.org
 */
interface FieldInterface
{
    /**
     * Returns the value
     *
     * @param array|\ArrayObject $context
     * @return mixed
     */
    public function getResult($context = null);
}
