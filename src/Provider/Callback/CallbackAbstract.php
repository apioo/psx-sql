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

namespace PSX\Sql\Provider\Callback;

use phpDocumentor\Reflection\Types\Callable_;
use PSX\Sql\Provider\ParameterResolver;

/**
 * CallbackAbstract
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    http://phpsx.org
 */
abstract class CallbackAbstract
{
    protected \Closure $callback;
    protected array $parameters;
    protected mixed $definition;

    public function __construct(callable $callback, array $parameters, mixed $definition)
    {
        $this->callback   = \Closure::fromCallable($callback);
        $this->parameters = $parameters;
        $this->definition = $definition;
    }

    public function getResult(array|\ArrayAccess|null $context = null): mixed
    {
        return call_user_func_array($this->callback, ParameterResolver::resolve($this->parameters, $context));
    }

    public function getDefinition(): mixed
    {
        return $this->definition;
    }
}
