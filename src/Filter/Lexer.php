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

use RuntimeException;

/**
 * Lexer
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://phpsx.org
 */
class Lexer
{
    public const T_IDENTIFIER = 'T_IDENTIFIER';
    public const T_STRING     = 'T_STRING';
    public const T_COLON      = 'T_COLON';
    public const T_AND        = 'T_AND';
    public const T_OR         = 'T_OR';
    public const T_NOT        = 'T_NOT';
    public const T_LPAREN     = 'T_LPAREN';
    public const T_RPAREN     = 'T_RPAREN';
    public const T_EOF        = 'T_EOF';

    private string $input;
    private int $pos = 0;
    private int $length;

    public function __construct(string $input)
    {
        $this->input  = $input;
        $this->length = strlen($input);
    }

    public function nextToken(): Token
    {
        $this->skipWhitespace();

        if ($this->pos >= $this->length) {
            return new Token(self::T_EOF);
        }

        $char = $this->input[$this->pos];

        if ($char === '(') {
            $this->pos++;
            return new Token(self::T_LPAREN);
        }

        if ($char === ')') {
            $this->pos++;
            return new Token(self::T_RPAREN);
        }

        if ($char === ':') {
            $this->pos++;
            return new Token(self::T_COLON);
        }

        if ($char === '"') {
            return $this->readString();
        }

        if (preg_match('/[a-zA-Z0-9_\-\.]/', $char)) {
            return $this->readIdentifier();
        }

        throw new RuntimeException('Unexpected character: ' . $char);
    }

    public function peekToken(): Token
    {
        $currentPos = $this->pos;
        $token = $this->nextToken();
        $this->pos = $currentPos;

        return $token;
    }

    private function skipWhitespace(): void
    {
        while (
            $this->pos < $this->length &&
            ctype_space($this->input[$this->pos])
        ) {
            $this->pos++;
        }
    }

    private function readString(): Token
    {
        $this->pos++; // skip opening quote
        $start = $this->pos;

        while (
            $this->pos < $this->length &&
            $this->input[$this->pos] !== '"'
        ) {
            $this->pos++;
        }

        $value = substr($this->input, $start, $this->pos - $start);

        if ($this->pos >= $this->length) {
            throw new RuntimeException('Unterminated string literal');
        }

        $this->pos++; // skip closing quote

        return new Token(self::T_STRING, $value);
    }

    private function readIdentifier(): Token
    {
        $start = $this->pos;

        while (
            $this->pos < $this->length &&
            preg_match('/[a-zA-Z0-9_\-\.]/', $this->input[$this->pos])
        ) {
            $this->pos++;
        }

        $value = substr($this->input, $start, $this->pos - $start);

        return match (strtoupper($value)) {
            'AND' => new Token(self::T_AND),
            'OR'  => new Token(self::T_OR),
            'NOT' => new Token(self::T_NOT),
            default => new Token(self::T_IDENTIFIER, $value),
        };
    }
}
