<?php
/*
 * PSX is an open source PHP framework to develop RESTful APIs.
 * For the current version and information visit <https://phpsx.org>
 *
 * Copyright (c) Christoph Kappestein <christoph.kappestein@gmail.com>
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
use PSX\DateTime\LocalDate;
use PSX\DateTime\LocalDateTime;
use PSX\DateTime\LocalTime;
use PSX\Sql\Condition\ExpressionAbstract;
use PSX\Sql\Condition\ExpressionInterface;
use PSX\Sql\Tests\ConditionTest;

/**
 * Condition which represents a SQL expression which filters a result set
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://phpsx.org
 */
class Condition extends ExpressionAbstract implements Countable
{
    private array $expressions = [];
    private LogicOperator $operator;
    private bool $inverse;

    public function __construct(array $conditions, LogicOperator $operator, bool $inverse = false)
    {
        parent::__construct('');

        $this->operator = $operator;
        $this->inverse = $inverse;

        foreach ($conditions as $condition) {
            if ($condition instanceof ExpressionInterface) {
                $this->add($condition);
            } else {
                throw new \InvalidArgumentException('Condition argument must be either a ' . ExpressionInterface::class);
            }
        }
    }

    /**
     * Adds a nexted condition
     */
    public function add(ExpressionInterface $expression): self
    {
        $this->expressions[] = $expression;

        return $this;
    }

    /**
     * Asserts that the column is equals to the value
     */
    public function equals(string $column, mixed $value): self
    {
        if ($value === null) {
            return $this->add(new Condition\Nil($column));
        } else {
            return $this->add(new Condition\Basic($column, ComparisonOperator::EQUALS, $value));
        }
    }

    /**
     * Asserts that the column is not equal to the value
     */
    public function notEquals(string $column, mixed $value): self
    {
        if ($value === null) {
            return $this->add(new Condition\NotNil($column));
        } else {
            return $this->add(new Condition\Basic($column, ComparisonOperator::NOT_EQUALS, $value));
        }
    }

    /**
     * Asserts that the column is greater then the value
     */
    public function greater(string $column, mixed $value): self
    {
        return $this->add(new Condition\Basic($column, ComparisonOperator::GREATER, $value));
    }

    /**
     * Asserts that the column is greater or equal to the value
     */
    public function greaterThan(string $column, mixed $value): self
    {
        return $this->add(new Condition\Basic($column, ComparisonOperator::GREATER_THAN, $value));
    }

    /**
     * Asserts that the column is lower then the value
     */
    public function less(string $column, mixed $value): self
    {
        return $this->add(new Condition\Basic($column, ComparisonOperator::LESS, $value));
    }

    /**
     * Asserts that the column is lower or equal to the value
     */
    public function lessThan(string $column, mixed $value): self
    {
        return $this->add(new Condition\Basic($column, ComparisonOperator::LESS_THAN, $value));
    }

    /**
     * Asserts that the column is like the value
     */
    public function like(string $column, mixed $value): self
    {
        return $this->add(new Condition\Basic($column, ComparisonOperator::LIKE, $value));
    }

    /**
     * Asserts that the column is not like the value
     */
    public function notLike(string $column, mixed $value): self
    {
        return $this->add(new Condition\Basic($column, ComparisonOperator::NOT_LIKE, $value));
    }

    /**
     * Asserts that the column is between the left and right value
     */
    public function between(string $column, mixed $left, mixed $right): self
    {
        return $this->add(new Condition\Between($column, $left, $right));
    }

    /**
     * Asserts that the column is in the array of values
     */
    public function in(string $column, array $values): self
    {
        return $this->add(new Condition\In($column, $values));
    }

    /**
     * Asserts that the column is in the array of values
     */
    public function notIn(string $column, array $values): self
    {
        return $this->add(new Condition\NotIn($column, $values));
    }

    /**
     * Asserts that the column is null
     */
    public function nil(string $column): self
    {
        return $this->add(new Condition\Nil($column));
    }

    /**
     * Asserts that the column is not null
     */
    public function notNil(string $column): self
    {
        return $this->add(new Condition\NotNil($column));
    }

    /**
     * Adds a raw SQL expression
     */
    public function raw(string $statement, array $values = []): self
    {
        return $this->add(new Condition\Raw($statement, $values));
    }

    /**
     * Asserts that the column matches the provided regular expression
     */
    public function regexp(string $column, string $regexp): self
    {
        return $this->add(new Condition\Regexp($column, $regexp));
    }

    /**
     * Sets whether the expression is inverse
     */
    public function setInverse(bool $inverse): self
    {
        $this->inverse = $inverse;

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
        if (empty($this->expressions)) {
            return $this->inverse ? '1 = 0' : '1 = 1';
        }

        $parts = [];
        foreach ($this->expressions as $expression) {
            $parts[] = $expression->getExpression($platform);
        }

        $con = implode(' ' . $this->operator->toSql() . ' ', $parts);

        return ($this->inverse ? 'NOT ' : '') . '(' . $con . ')';
    }

    /**
     * Returns the parameters as array
     *
     * @return array<int<0, max>, mixed>
     */
    public function getValues(): array
    {
        $params = [];
        foreach ($this->expressions as $expression) {
            $values = $expression->getValues();
            foreach ($values as $value) {
                if ($value instanceof LocalDate || $value instanceof LocalDateTime || $value instanceof LocalTime) {
                    $params[] = $value->toString();
                } elseif ($value instanceof \DateTime) {
                    $params[] = $value->format('Y-m-d H:i:s');
                } else {
                    $params[] = $value;
                }
            }
        }

        return $params;
    }

    public static function withAnd(ExpressionInterface ...$conditions): self
    {
        return new self($conditions, LogicOperator::AND);
    }

    public static function withOr(ExpressionInterface ...$conditions): self
    {
        return new self($conditions, LogicOperator::OR);
    }

    public static function fromCriteria(array $criteria): self
    {
        $condition = self::withAnd();

        foreach ($criteria as $field => $value) {
            if (is_array($value)) {
                $condition->add(new Condition\In($field, $value));
            } elseif (is_null($value)) {
                $condition->add(new Condition\Nil($field));
            } elseif (is_scalar($value)) {
                $condition->add(new Condition\Basic($field, ComparisonOperator::EQUALS, $value));
            }
        }

        return $condition;
    }
}
