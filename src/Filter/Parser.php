<?php
/*
 * PSX is an open source PHP framework to develop RESTful APIs.
 * For the current version and information visit <https://phpsx.org>
 *
 * Copyright 2010-2023 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace PSX\Sql\Filter;

use PSX\Sql\Filter\Node\AndNode;
use PSX\Sql\Filter\Node\ConditionNode;
use PSX\Sql\Filter\Node\Node;
use PSX\Sql\Filter\Node\NotNode;
use PSX\Sql\Filter\Node\OrNode;
use RuntimeException;

/**
 * Parser
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://phpsx.org
 */
class Parser
{
    private Lexer $lexer;
    private Token $currentToken;

    public function __construct(Lexer $lexer)
    {
        $this->lexer = $lexer;
        $this->currentToken = $lexer->nextToken();
    }

    public function parse(): Node
    {
        $node = $this->parseOr();

        if ($this->currentToken->type !== Lexer::T_EOF) {
            throw new RuntimeException(
                "Unexpected token {$this->currentToken->type}"
            );
        }

        return $node;
    }

    private function eat(string $type): void
    {
        if ($this->currentToken->type === $type) {
            $this->currentToken = $this->lexer->nextToken();
        } else {
            throw new RuntimeException(
                "Expected {$type}, got {$this->currentToken->type}"
            );
        }
    }

    private function parseOr(): Node
    {
        $node = $this->parseAnd();

        while ($this->currentToken->type === Lexer::T_OR) {
            $this->eat(Lexer::T_OR);
            $node = new OrNode(
                $node,
                $this->parseAnd()
            );
        }

        return $node;
    }

    private function parseAnd(): Node
    {
        $node = $this->parseNot();

        while ($this->currentToken->type === Lexer::T_AND) {
            $this->eat(Lexer::T_AND);
            $node = new AndNode(
                $node,
                $this->parseNot()
            );
        }

        return $node;
    }

    private function parseNot(): Node
    {
        if ($this->currentToken->type === Lexer::T_NOT) {
            $this->eat(Lexer::T_NOT);

            return new NotNode(
                $this->parseNot()
            );
        }

        return $this->parsePrimary();
    }

    private function parsePrimary(): Node
    {
        if ($this->currentToken->type === Lexer::T_LPAREN) {
            $this->eat(Lexer::T_LPAREN);
            $node = $this->parseOr();
            $this->eat(Lexer::T_RPAREN);
            return $node;
        }

        if ($this->currentToken->type === Lexer::T_IDENTIFIER) {
            $next = $this->lexer->peekToken();

            if ($next->type === Lexer::T_COLON) {
                return $this->parseCondition();
            }

            // Bare identifier
            $value = $this->currentToken->value;
            $this->eat(Lexer::T_IDENTIFIER);

            return new ConditionNode('_default', $value);
        }

        if ($this->currentToken->type === Lexer::T_STRING) {
            $value = $this->currentToken->value;
            $this->eat(Lexer::T_STRING);

            return new ConditionNode('_default', $value);
        }

        throw new RuntimeException(
            "Unexpected token {$this->currentToken->type}"
        );
    }

    private function parseCondition(): Node
    {
        $field = $this->currentToken->value;
        $this->eat(Lexer::T_IDENTIFIER);

        $this->eat(Lexer::T_COLON);

        $value = $this->currentToken->value;

        if ($this->currentToken->type === Lexer::T_STRING) {
            $this->eat(Lexer::T_STRING);
        } else {
            $this->eat(Lexer::T_IDENTIFIER);
        }

        return new ConditionNode($field, $value);
    }
}
