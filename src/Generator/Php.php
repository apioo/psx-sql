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

namespace PSX\Sql\Generator;

use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Table;
use PhpParser\BuilderFactory;
use PhpParser\Node;
use PhpParser\PrettyPrinter;
use PSX\Sql\GeneratorInterface;
use PSX\Sql\TableInterface;
use PSX\Sql\TypeMapper;

/**
 * Php
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    http://phpsx.org
 */
class Php implements GeneratorInterface
{
    /**
     * @var \PhpParser\BuilderFactory
     */
    protected $factory;

    /**
     * @var string
     */
    protected $namespace;

    /**
     * @var \PhpParser\PrettyPrinter\Standard
     */
    protected $printer;

    public function __construct($namespace = null)
    {
        $this->factory   = new BuilderFactory();
        $this->namespace = $namespace === null ? 'PSX\Generation' : $namespace;
        $this->printer   = new PrettyPrinter\Standard();
    }

    public function generate(Table $table)
    {
        $root = $this->factory->namespace($this->namespace);
        $class = $this->factory->class($this->getClassNameByTable($table->getName()))
            ->extend('\PSX\Sql\TableAbstract');

        $class->addStmt($this->factory->method('getName')
            ->makePublic()
            ->addStmt(new Node\Stmt\Return_(new Node\Scalar\String_($table->getName())))
        );

        $items = [];
        foreach ($table->getColumns() as $column) {
            $items[] = new Node\Expr\ArrayItem(
                $this->getColumn($table, $column),
                new Node\Scalar\String_($column->getName())
            );
        }

        $class->addStmt($this->factory->method('getColumns')
            ->makePublic()
            ->addStmt(new Node\Stmt\Return_(new Node\Expr\Array_($items)))
        );

        $root->addStmt($class);

        return $this->printer->prettyPrintFile([$root->getNode()]);
    }

    protected function getColumn(Table $table, Column $column)
    {
        $type     = TypeMapper::getTypeByDoctrineType($column->getType()->getName());
        $constant = $this->getConstantNameByValue($type);

        if (empty($constant)) {
            throw new \RuntimeException('Could not determine type for column ' . $column->getName());
        }

        $value = new Node\Expr\ClassConstFetch(new Node\Name('self'), $constant);

        if ($column->getLength() > 0) {
            $value = new Node\Expr\BinaryOp\BitwiseOr(
                $value,
                new Node\Scalar\LNumber($column->getLength())
            );
        }

        if (in_array($column->getName(), $table->getPrimaryKeyColumns())) {
            $value = new Node\Expr\BinaryOp\BitwiseOr(
                $value,
                new Node\Expr\ClassConstFetch(new Node\Name('self'), 'PRIMARY_KEY')
            );
        }

        if (!$column->getNotnull()) {
            $value = new Node\Expr\BinaryOp\BitwiseOr(
                $value,
                new Node\Expr\ClassConstFetch(new Node\Name('self'), 'IS_NULL')
            );
        }

        if ($column->getAutoincrement()) {
            $value = new Node\Expr\BinaryOp\BitwiseOr(
                $value,
                new Node\Expr\ClassConstFetch(new Node\Name('self'), 'AUTO_INCREMENT')
            );
        }

        if ($column->getUnsigned()) {
            $value = new Node\Expr\BinaryOp\BitwiseOr(
                $value,
                new Node\Expr\ClassConstFetch(new Node\Name('self'), 'UNSIGNED')
            );
        }

        return $value;
    }

    protected function getConstantNameByValue($type)
    {
        $reflection = new \ReflectionClass(TableInterface::class);
        $constants  = $reflection->getConstants();

        foreach ($constants as $constant => $value) {
            if ($type === $value) {
                return $constant;
            }
        }

        return null;
    }

    protected function getClassNameByTable($tableName)
    {
        $pos       = strrpos($tableName, '_');
        $className = $pos !== false ? substr($tableName, $pos + 1) : $tableName;

        return ucfirst($className);
    }
}
