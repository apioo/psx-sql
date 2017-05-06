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

namespace PSX\Sql\Field;

/**
 * DateTime
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    http://phpsx.org
 */
class DateTime extends TransformFieldAbstract
{
    protected function transform($value)
    {
        if (!$value instanceof \DateTime) {
            $data = (string) $value;
            if (empty($data) || $data === '0000-00-00 00:00:00') {
                return null;
            } else {
                $value = new \DateTime($data);
            }
        }

        return $value->getOffset() == 0 ? $value->format('Y-m-d\TH:i:s') . 'Z' : $value->format(\DateTime::RFC3339);
    }
}
