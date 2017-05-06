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

namespace PSX\Sql\Provider;

use PSX\Sql\ProviderInterface;

/**
 * ProviderCollectionInterface
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    http://phpsx.org
 */
interface ProviderCollectionInterface extends ProviderInterface
{
    /**
     * Returns the name of the field which should be used as key. If the key is
     * a callable the function can generate dynamic keys. Return null to build 
     * an indexed array
     *
     * @return string|callable|null
     */
    public function getKey();

    /**
     * Returns a callback which can filter the complete result of a collection
     *
     * @return callable|null
     */
    public function getFilter();
}
