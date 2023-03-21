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

use Countable;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use InvalidArgumentException;
use PSX\Sql\Condition\ExpressionAbstract;
use PSX\Sql\Condition\ExpressionInterface;

/**
 * Condition which represents a SQL expression which filters a result set
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://phpsx.org
 */
class Condition extends ExpressionAbstract implements Countable
{
    private const ARITHMETIC_OPERATOR = ['=', 'IS', '!=', 'IS NOT', 'LIKE', 'NOT LIKE', '<', '>', '<=', '>=', 'IN'];
    private const LOGIC_OPERATOR      = ['AND', 'OR', '&&', '||'];

    private array $expressions = [];
    private bool $isInverse = false;

    public function __construct(...$conditions)
    {
        foreach ($conditions as $condition) {
            if (is_array($condition)) {
                $this->add(...$condition);
            } elseif ($condition instanceof ExpressionInterface) {
                $this->addExpression($condition);
            } else {
                throw new \InvalidArgumentException('Condition argument must be either an array or ' . ExpressionInterface::class);
            }
        }
    }

    /**
     * Adds a condition and tries to detect the type of the condition based on
     * the provided values. It is recommended to use an explicit method
     */
    public function add(string $column, string $operator, mixed $value, string $conjunction = 'AND'): self
    {
        if (!in_array($operator, self::ARITHMETIC_OPERATOR)) {
            throw new InvalidArgumentException('Invalid arithmetic operator (allowed: ' . implode(', ', self::ARITHMETIC_OPERATOR) . ')');
        }

        if (!in_array($conjunction, self::LOGIC_OPERATOR)) {
            throw new InvalidArgumentException('Invalid logic operator (allowed: ' . implode(', ', self::LOGIC_OPERATOR) . ')');
        }

        if ($operator === 'IN' && is_array($value)) {
            $expr = new Condition\In($column, $value, $conjunction);
        } elseif (($operator === '=' || $operator === 'IS') && $value === null) {
            $expr = new Condition\Nil($column, $conjunction);
        } elseif (($operator === '!=' || $operator === 'IS NOT') && $value === null) {
            $expr = new Condition\NotNil($column, $conjunction);
        } else {
            $expr = new Condition\Basic($column, $operator, $value, $conjunction);
        }

        return $this->addExpression($expr);
    }

    /**
     * Asserts that the column is equals to the value
     */
    public function equals(string $column, mixed $value, string $conjunction = 'AND'): self
    {
        return $this->addExpression(new Condition\Basic($column, '=', $value, $conjunction));
    }

    /**
     * Asserts that the column is not equal to the value
     */
    public function notEquals(string $column, mixed $value, string $conjunction = 'AND'): self
    {
        return $this->addExpression(new Condition\Basic($column, '!=', $value, $conjunction));
    }

    /**
     * Asserts that the column is greater then the value
     */
    public function greater(string $column, mixed $value, string $conjunction = 'AND'): self
    {
        return $this->addExpression(new Condition\Basic($column, '>', $value, $conjunction));
    }

    /**
     * Asserts that the column is greater or equal to the value
     */
    public function greaterThen(string $column, mixed $value, string $conjunction = 'AND'): self
    {
        return $this->addExpression(new Condition\Basic($column, '>=', $value, $conjunction));
    }

    /**
     * Asserts that the column is lower then the value
     */
    public function lower(string $column, mixed $value, string $conjunction = 'AND'): self
    {
        return $this->addExpression(new Condition\Basic($column, '<', $value, $conjunction));
    }

    /**
     * Asserts that the column is lower or equal to the value
     */
    public function lowerThen(string $column, mixed $value, string $conjunction = 'AND'): self
    {
        return $this->addExpression(new Condition\Basic($column, '<=', $value, $conjunction));
    }

    /**
     * Asserts that the column is like the value
     */
    public function like(string $column, mixed $value, string $conjunction = 'AND'): self
    {
        return $this->addExpression(new Condition\Basic($column, 'LIKE', $value, $conjunction));
    }

    /**
     * Asserts that the column is not like the value
     */
    public function notLike(string $column, mixed $value, string $conjunction = 'AND'): self
    {
        return $this->addExpression(new Condition\Basic($column, 'NOT LIKE', $value, $conjunction));
    }

    /**
     * Asserts that the column is between the left and right value
     */
    public function between(string $column, mixed $left, mixed $right, string $conjunction = 'AND'): self
    {
        return $this->addExpression(new Condition\Between($column, $left, $right, $conjunction));
    }

    /**
     * Asserts that the column is in the array of values
     */
    public function in(string $column, array $values, string $conjunction = 'AND'): self
    {
        return $this->addExpression(new Condition\In($column, $values, $conjunction));
    }

    /**
     * Asserts that the column is null
     */
    public function nil(string $column, string $conjunction = 'AND'): self
    {
        return $this->addExpression(new Condition\Nil($column, $conjunction));
    }

    /**
     * Asserts that the column is not null
     */
    public function notNil(string $column, string $conjunction = 'AND'): self
    {
        return $this->addExpression(new Condition\NotNil($column, $conjunction));
    }

    /**
     * Adds a raw SQL expression
     */
    public function raw(string $statement, array $values = [], string $conjunction = 'AND'): self
    {
        return $this->addExpression(new Condition\Raw($statement, $values, $conjunction));
    }

    /**
     * Asserts that the column matches the provided regular expression
     */
    public function regexp(string $column, string $regexp, string $conjunction = 'AND'): self
    {
        return $this->addExpression(new Condition\Regexp($column, $regexp, $conjunction));
    }

    /**
     * Adds an expression
     */
    public function addExpression(ExpressionInterface $expr): self
    {
        $this->expressions[] = $expr;

        return $this;
    }

    /**
     * Sets whether the expression is inverse
     */
    public function setInverse(bool $isInverse): self
    {
        $this->isInverse = $isInverse;

        return $this;
    }

    /**
     * Returns the count of conditions
     */
    public function count(): int
    {
        return count($this->expressions);
    }

    /**
     * Merges an existing condition
     */
    public function merge(Condition $condition): self
    {
        $this->expressions = array_merge($this->expressions, $condition->toArray());

        return $this;
    }

    /**
     * Returns an expression by the column name or null
     */
    public function get(string $column): ?ExpressionInterface
    {
        foreach ($this->expressions as $expression) {
            if ($expression->getColumn() == $column) {
                return $expression;
            }
        }

        return null;
    }

    /**
     * Removes an condition containing an specific column
     */
    public function remove(string $column): void
    {
        foreach ($this->expressions as $key => $expression) {
            if ($expression->getColumn() == $column) {
                unset($this->expressions[$key]);
            }
        }
    }

    /**
     * Removes all columns
     */
    public function removeAll(): void
    {
        $this->expressions = [];
    }

    /**
     * Returns all conditions as array
     */
    public function toArray(): array
    {
        return $this->expressions;
    }

    /**
     * Returns whether an condition exist
     */
    public function hasCondition(): bool
    {
        return count($this->expressions) > 0;
    }

    public function getStatement(?AbstractPlatform $platform = null): string
    {
        if ($platform === null) {
            $platform = new MySQLPlatform();
        }

        return 'WHERE ' . $this->getExpression($platform);
    }

    /**
     * Returns the SQL as string containing prepared statement placeholder
     */
    public function getExpression(AbstractPlatform $platform): string
    {
        $len = count($this->expressions);
        $con = '';
        $i   = 0;

        if (empty($this->expressions)) {
            return $this->isInverse ? '1 = 0' : '1 = 1';
        }

        foreach ($this->expressions as $expression) {
            $con.= $expression->getExpression($platform);
            $con.= ($i == $len - 1) ? '' : ' ' . $expression->getConjunction() . ' ';

            $i++;
        }

        return ($this->isInverse ? '!' : '') . '(' . $con . ')';
    }

    /**
     * Returns the parameters as array
     *
     * @return array<int, mixed>
     */
    public function getValues(): array
    {
        $params = [];
        foreach ($this->expressions as $expression) {
            $values = $expression->getValues();
            foreach ($values as $value) {
                if ($value instanceof \DateTime) {
                    $params[] = $value->format('Y-m-d H:i:s');
                } else {
                    $params[] = $value;
                }
            }
        }

        return $params;
    }

    public function getArray(): array
    {
        $result = [];
        foreach ($this->expressions as $expression) {
            if ($expression instanceof Condition\In) {
                $result[$expression->getColumn()] = $expression->getValues();
            } elseif ($expression instanceof Condition\Nil) {
                $result[$expression->getColumn()] = null;
            } elseif ($expression instanceof Condition\Basic) {
                $result[$expression->getColumn()] = current($expression->getValues());
            }
        }

        return $result;
    }

    public static function fromCriteria(array $criteria): self
    {
        $condition = new self();

        foreach ($criteria as $field => $value) {
            if (is_array($value)) {
                $condition->addExpression(new Condition\In($field, $value));
            } elseif (is_null($value)) {
                $condition->addExpression(new Condition\Nil($field));
            } elseif (is_scalar($value)) {
                $condition->addExpression(new Condition\Basic($field, '=', $value));
            }
        }

        return $condition;
    }
}
