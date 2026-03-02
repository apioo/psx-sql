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

use PSX\Sql\ColumnInterface;
use PSX\Sql\Condition;
use PSX\Sql\Filter\Node\AndNode;
use PSX\Sql\Filter\Node\ComparisonNode;
use PSX\Sql\Filter\Node\NotNode;
use PSX\Sql\Filter\Node\OrNode;
use PSX\Sql\TableInterface;

/**
 * Builder
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://phpsx.org
 */
class Builder
{
    public function build(TableInterface $table, ColumnInterface $defaultColumn, string $search): Condition
    {
        $parser = new Parser(new Lexer($search));
        $ast = $parser->parse();

        $condition = Condition::withAnd();
        $this->recBuild($ast, $table, $defaultColumn->value, $condition);

        return $condition;
    }

    private function recBuild(Node\Node $node, TableInterface $table, string $defaultColumn, Condition $condition): void
    {
        if ($node instanceof AndNode) {
            $andCondition = Condition::withAnd();
            $this->recBuild($node->left, $table, $defaultColumn, $andCondition);
            $this->recBuild($node->right, $table, $defaultColumn, $andCondition);
            $condition->add($andCondition);
        } elseif ($node instanceof OrNode) {
            $orCondition = Condition::withOr();
            $this->recBuild($node->left, $table, $defaultColumn, $orCondition);
            $this->recBuild($node->right, $table, $defaultColumn, $orCondition);
            $condition->add($orCondition);
        } elseif ($node instanceof NotNode) {
            $andCondition = Condition::withAnd();
            $andCondition->setInverse(true);
            $this->recBuild($node->operand, $table, $defaultColumn, $andCondition);
            $condition->add($andCondition);
        } elseif ($node instanceof ComparisonNode) {
            $field = $node->field;
            if ($field === '_default') {
                $field = $defaultColumn;
            }

            $column = $table->getColumns()[$field] ?? null;
            if ($column === null) {
                return;
            }

            if (in_array($node->operator, ['>', '<']) && $this->isOfType($column, [TableInterface::TYPE_SMALLINT, TableInterface::TYPE_INT, TableInterface::TYPE_BIGINT, TableInterface::TYPE_DECIMAL, TableInterface::TYPE_FLOAT, TableInterface::TYPE_DATE, TableInterface::TYPE_DATETIME])) {
                if ($node->operator === '>') {
                    $condition->greater($field, $node->value);
                } elseif ($node->operator === '<') {
                    $condition->less($field, $node->value);
                }
            } elseif ($this->isOfType($column, [TableInterface::TYPE_VARCHAR, TableInterface::TYPE_TEXT, TableInterface::TYPE_JSON])) {
                $condition->like($field, $node->value);
            } else {
                $condition->equals($field, $node->value);
            }
        }
    }

    private function isOfType(int $column, array $types): bool
    {
        foreach ($types as $type) {
            if (($column & $type) === $type) {
                return true;
            }
        }

        return false;
    }
}
