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

namespace PSX\Sql\Filter;

use Doctrine\DBAL\Schema\Exception\ColumnDoesNotExist;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\BigIntType;
use Doctrine\DBAL\Types\DateTimeType;
use Doctrine\DBAL\Types\DateType;
use Doctrine\DBAL\Types\DecimalType;
use Doctrine\DBAL\Types\FloatType;
use Doctrine\DBAL\Types\IntegerType;
use Doctrine\DBAL\Types\JsonType;
use Doctrine\DBAL\Types\SmallIntType;
use Doctrine\DBAL\Types\StringType;
use Doctrine\DBAL\Types\TextType;
use Doctrine\DBAL\Types\Type;
use PSX\Sql\Condition;
use PSX\Sql\Filter\Node\AndNode;
use PSX\Sql\Filter\Node\ComparisonNode;
use PSX\Sql\Filter\Node\NotNode;
use PSX\Sql\Filter\Node\OrNode;

/**
 * DoctrineBuilder
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://phpsx.org
 */
class DoctrineBuilder
{
    public function build(Table $table, string $defaultColumn, string $search, ?string $alias = null): Condition
    {
        $parser = new Parser(new Lexer($search));
        $ast = $parser->parse();

        $condition = Condition::withAnd();
        $this->recBuild($ast, $table, $defaultColumn, $condition, $alias);

        return $condition;
    }

    private function recBuild(Node\Node $node, Table $table, string $defaultColumn, Condition $condition, ?string $alias): void
    {
        if ($node instanceof AndNode) {
            $andCondition = Condition::withAnd();
            $this->recBuild($node->left, $table, $defaultColumn, $andCondition, $alias);
            $this->recBuild($node->right, $table, $defaultColumn, $andCondition, $alias);
            $condition->add($andCondition);
        } elseif ($node instanceof OrNode) {
            $orCondition = Condition::withOr();
            $this->recBuild($node->left, $table, $defaultColumn, $orCondition, $alias);
            $this->recBuild($node->right, $table, $defaultColumn, $orCondition, $alias);
            $condition->add($orCondition);
        } elseif ($node instanceof NotNode) {
            $andCondition = Condition::withAnd();
            $andCondition->setInverse(true);
            $this->recBuild($node->operand, $table, $defaultColumn, $andCondition, $alias);
            $condition->add($andCondition);
        } elseif ($node instanceof ComparisonNode) {
            $field = $node->field;
            if ($field === '_default') {
                $field = $defaultColumn;
            }

            try {
                $column = $table->getColumn($field);
            } catch (ColumnDoesNotExist) {
                return;
            }

            $columnAlias = $this->getAlias($alias);
            $type = $column->getType();

            if (in_array($node->operator, ['>', '<']) && $this->isOfType($type, [SmallIntType::class, IntegerType::class, BigIntType::class, DecimalType::class, FloatType::class, DateType::class, DateTimeType::class])) {
                if ($node->operator === '>') {
                    $condition->greater($columnAlias . $field, $node->value);
                } elseif ($node->operator === '<') {
                    $condition->less($columnAlias . $field, $node->value);
                }
            } elseif ($this->isOfType($type, [StringType::class, TextType::class, JsonType::class])) {
                $condition->like($columnAlias . $field, '%' . $node->value . '%');
            } else {
                $condition->equals($columnAlias . $field, $node->value);
            }
        }
    }

    /**
     * @param list<class-string<Type>> $typeClasses
     */
    private function isOfType(Type $column, array $typeClasses): bool
    {
        foreach ($typeClasses as $typeClass) {
            if ($column instanceof $typeClass) {
                return true;
            }
        }

        return false;
    }

    private function getAlias(?string $alias): string
    {
        return $alias !== null ? $alias . '.' : '';
    }
}
