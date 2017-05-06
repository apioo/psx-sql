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

use Doctrine\DBAL\Connection;
use PSX\Sql\TypeMapper;

/**
 * Type
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    http://phpsx.org
 */
class Type extends TransformFieldAbstract
{
    /**
     * @var \Doctrine\DBAL\Connection
     */
    protected $connection;

    /**
     * @var integer
     */
    protected $type;

    public function __construct($field, Connection $connection, $type)
    {
        parent::__construct($field);

        $this->connection = $connection;
        $this->type       = $type;
    }

    protected function transform($value)
    {
        $type = (($this->type >> 20) & 0xFF) << 20;
        $type = TypeMapper::getDoctrineTypeByType($type);

        return $this->connection->convertToPHPValue($value, $type);
    }
}
