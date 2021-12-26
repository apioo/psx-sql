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

namespace PSX\Sql;

use Doctrine\DBAL\Logging\SQLLogger;
use Psr\Log\LoggerInterface;

/**
 * Logger
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @author  Fabien Potencier <fabien@symfony.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://phpsx.org
 */
class Logger implements SQLLogger
{
    const MAX_STRING_LENGTH = 32;
    const BINARY_DATA_VALUE = '(binary value)';

    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function startQuery($sql, array $params = null, array $types = null)
    {
        if (is_array($params)) {
            foreach ($params as $key => $value) {
                // non utf-8 strings break json encoding
                if (!preg_match('//u', $params[$key])) {
                    $params[$key] = self::BINARY_DATA_VALUE;
                    continue;
                }

                // detect if the too long string must be shorten
                if (self::MAX_STRING_LENGTH < strlen($params[$key])) {
                    $params[$key] = substr($params[$key], 0, self::MAX_STRING_LENGTH - 6).' [...]';
                    continue;
                }
            }
        }

        $this->logger->debug($sql, $params === null ? array() : $params);
    }

    public function stopQuery()
    {
    }
}
