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

namespace PSX\Sql\Generator;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Index;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types;
use PhpParser\Builder\Class_;
use PhpParser\Builder\Declaration;
use PhpParser\Builder\Enum_;
use PhpParser\BuilderFactory;
use PhpParser\Comment\Doc;
use PhpParser\Modifiers;
use PhpParser\Node;
use PhpParser\PrettyPrinter;
use PSX\DateTime\LocalDate;
use PSX\DateTime\LocalDateTime;
use PSX\DateTime\LocalTime;
use PSX\DateTime\Period;
use PSX\Record\Record;
use PSX\Record\RecordableInterface;
use PSX\Record\RecordInterface;
use PSX\Sql\ColumnInterface;
use PSX\Sql\Condition;
use PSX\Sql\Exception\GeneratorException;
use PSX\Sql\Exception\ManipulationException;
use PSX\Sql\Exception\NoValueAvailable;
use PSX\Sql\Exception\QueryException;
use PSX\Sql\OrderBy;
use PSX\Sql\TableAbstract;
use PSX\Sql\TableInterface;
use PSX\Sql\TypeMapper;

/**
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://phpsx.org
 */
class Generator
{
    private Connection $connection;
    private ?string $namespace;
    private ?string $prefix;
    private BuilderFactory $factory;
    private PrettyPrinter\Standard $printer;

    public function __construct(Connection $connection, ?string $namespace = null, ?string $prefix = null)
    {
        $this->connection = $connection;
        $this->namespace = $namespace;
        $this->prefix = $prefix;
        $this->factory = new BuilderFactory();
        $this->printer = new PrettyPrinter\Standard();
    }

    public function generate(): \Generator
    {
        $schemaManager = $this->connection->createSchemaManager();
        $tableNames = $schemaManager->listTableNames();
        
        foreach ($tableNames as $tableName) {
            $table = $schemaManager->introspectTable($tableName);

            $tableName = $table->getName();
            if ($this->prefix !== null) {
                if (!str_starts_with($tableName, $this->prefix)) {
                    // if the table does not start with the prefix ignore
                    continue;
                }

                $tableName = substr($tableName, strlen($this->prefix));
            }

            $modelClassName = $this->normalizeName($tableName) . 'Row';
            $repositoryClassName = $this->normalizeName($tableName) . 'Table';
            $columnClassName = $this->normalizeName($tableName) . 'Column';

            $class = $this->generateModel($modelClassName, $table);
            yield $modelClassName => $this->prettyPrint($class);

            $class = $this->generateRepository($repositoryClassName, $modelClassName, $columnClassName, $table);
            yield $repositoryClassName => $this->prettyPrint($class);

            $class = $this->generateColumn($columnClassName, $repositoryClassName, $table);
            yield $columnClassName => $this->prettyPrint($class);
        }
    }

    private function generateModel(string $className, Table $table): Class_
    {
        $class = $this->factory->class($className);
        $class->implement('\\JsonSerializable', '\\' . RecordableInterface::class);

        $serialize = [];
        $columns = $table->getColumns();
        foreach ($columns as $column) {
            $name = lcfirst($this->normalizeName($column->getName()));
            $type = $this->getTypeForColumn($column);
            $isNullable = $column->getNotnull() === false;

            $serialize[$name] = $column;

            $prop = $this->factory->property($name);
            $prop->makePrivate();
            if ($type === 'mixed') {
                $prop->setType($type);
            } else {
                $prop->setType('?' . $type);
            }
            $prop->setDefault(null);
            $class->addStmt($prop);

            // setter
            $param = $this->factory->param($name);
            if (!empty($type)) {
                if ($type !== 'mixed') {
                    $param->setType($isNullable ? '?' . $type : $type);
                } else {
                    $param->setType($type);
                }
            }

            $setter = $this->factory->method('set' . ucfirst($name));
            $setter->setReturnType('void');
            $setter->makePublic();
            $setter->addParam($param);
            $setter->addStmt(new Node\Expr\Assign(
                new Node\Expr\PropertyFetch(new Node\Expr\Variable('this'), $name),
                new Node\Expr\Variable($name)
            ));
            $class->addStmt($setter);

            // getter
            $getter = $this->factory->method('get' . ucfirst($name));
            if (!empty($type)) {
                if ($type !== 'mixed') {
                    $getter->setReturnType($isNullable ? '?' . $type : $type);
                } else {
                    $getter->setReturnType($type);
                }
            } else {
                $getter->setReturnType('void');
            }

            $getter->makePublic();

            if ($isNullable) {
                $fetch = new Node\Expr\PropertyFetch(new Node\Expr\Variable('this'), $name);
            } else {
                $fetch = new Node\Expr\BinaryOp\Coalesce(new Node\Expr\PropertyFetch(new Node\Expr\Variable('this'), $name), new Node\Expr\Throw_(new Node\Expr\New_(new Node\Name\FullyQualified(NoValueAvailable::class), [new Node\Arg(new Node\Scalar\String_('No value for required column "' . $column->getName() . '" was provided'))])));
            }

            $getter->addStmt(new Node\Stmt\Return_($fetch));

            $class->addStmt($getter);
        }

        $this->buildToRecord($class, $serialize);
        $this->buildJsonSerialize($class);
        $this->buildFrom($class, $serialize);

        return $class;
    }

    private function buildToRecord(Class_ $class, array $columns): void
    {
        if (empty($columns)) {
            return;
        }

        $stmts = [];
        $stmts[] = new Node\Stmt\Expression(
            new Node\Expr\Assign(new Node\Expr\Variable('record'), new Node\Expr\New_(new Node\Name('\\' . Record::class))),
            ['comments' => [new Doc('/** @var \PSX\Record\Record<mixed> $record */')]]
        );

        foreach ($columns as $name => $column) {
            $stmts[] = new Node\Stmt\Expression(new Node\Expr\MethodCall(new Node\Expr\Variable('record'), new Node\Identifier('put'), [
                new Node\Arg(new Node\Scalar\String_($column->getName())),
                new Node\Arg(new Node\Expr\PropertyFetch(new Node\Expr\Variable('this'), $name)),
            ]));
        }

        $stmts[] = new Node\Stmt\Return_(new Node\Expr\Variable('record'));

        $toRecord = $this->factory->method('toRecord');
        $toRecord->makePublic();
        $toRecord->setReturnType('\\' . RecordInterface::class);
        $toRecord->addStmts($stmts);

        $class->addStmt($toRecord);
    }

    private function buildJsonSerialize(Class_ $class): void
    {
        $toRecord = new Node\Expr\MethodCall(
            new Node\Expr\MethodCall(
                new Node\Expr\Variable('this'),
                new Node\Identifier('toRecord')
            ),
            new Node\Identifier('getAll')
        );

        $serialize = $this->factory->method('jsonSerialize');
        $serialize->makePublic();
        $serialize->setReturnType('object');
        $serialize->addStmt(new Node\Stmt\Return_(new Node\Expr\Cast\Object_($toRecord)));

        $class->addStmt($serialize);
    }

    private function buildFrom(Class_ $class, array $columns): void
    {
        if (empty($columns)) {
            return;
        }

        $stmts = [];
        $stmts[] = new Node\Stmt\Expression(new Node\Expr\Assign(new Node\Expr\Variable('row'), new Node\Expr\New_(new Node\Name('self'))));

        foreach ($columns as $name => $column) {
            $fetch = new Node\Expr\ArrayDimFetch(new Node\Expr\Variable('data'), new Node\Scalar\String_($column->getName()));
            if ($column->getType() instanceof Types\DateType) {
                $arg = new Node\Expr\StaticCall(new Node\Name('\\' . LocalDate::class), new Node\Identifier('from'), [new Node\Arg($fetch)]);
            } elseif ($column->getType() instanceof Types\DateTimeType) {
                $arg = new Node\Expr\StaticCall(new Node\Name('\\' . LocalDateTime::class), new Node\Identifier('from'), [new Node\Arg($fetch)]);
            } elseif ($column->getType() instanceof Types\TimeType) {
                $arg = new Node\Expr\StaticCall(new Node\Name('\\' . LocalTime::class), new Node\Identifier('from'), [new Node\Arg($fetch)]);
            } else {
                $arg = $fetch;
            }

            $validatorMethod = $this->getValidatorForType($column);
            if ($validatorMethod !== null) {
                if ($validatorMethod[0] === '\\') {
                    $arg = new Node\Expr\Ternary(new Node\Expr\BinaryOp\BooleanAnd(new Node\Expr\Isset_([$fetch]), new Node\Expr\Instanceof_($fetch, new Node\Name\FullyQualified(substr($validatorMethod, 1)))), $arg, new Node\Expr\ConstFetch(new Node\Name('null')));
                } else {
                    $arg = new Node\Expr\Ternary(new Node\Expr\BinaryOp\BooleanAnd(new Node\Expr\Isset_([$fetch]), new Node\Expr\FuncCall(new Node\Name($validatorMethod), [new Node\Arg($fetch)])), $arg, new Node\Expr\ConstFetch(new Node\Name('null')));
                }
            } else {
                $arg = new Node\Expr\Ternary(new Node\Expr\Isset_([$fetch]), $arg, new Node\Expr\ConstFetch(new Node\Name('null')));
            }

            $stmts[] = new Node\Stmt\Expression(new Node\Expr\Assign(new Node\Expr\PropertyFetch(new Node\Expr\Variable('row'), new Node\Identifier($name)), $arg));
        }

        $stmts[] = new Node\Stmt\Return_(new Node\Expr\Variable('row'));

        $param = $this->factory->param('data');
        $param->setType('array|\\' . \ArrayAccess::class);

        $fromArray = $this->factory->method('from');
        $fromArray->makeStatic();
        $fromArray->makePublic();
        $fromArray->addParam($param);
        $fromArray->setReturnType('self');
        $fromArray->addStmts($stmts);

        $class->addStmt($fromArray);
    }

    private function generateRepository(string $className, string $rowClass, string $columnClass, Table $table): Class_
    {
        $rowClass = $this->namespace !== null ? '\\' . $this->namespace . '\\' . $rowClass : '\\' . $rowClass;
        $columnClass = $this->namespace !== null ? '\\' . $this->namespace . '\\' . $columnClass : '\\' . $columnClass;

        $class = $this->factory->class($className);
        $class->extend('\\' . TableAbstract::class);
        $class->setDocComment($this->buildComment(['extends' => '\\' . TableAbstract::class . '<' . $rowClass . '>']));

        $this->buildConstants($class, $table);
        $this->buildGetName($class);
        $this->buildGetColumns($class, $table);
        $this->buildFindAll($class, $rowClass, $columnClass);
        $this->buildFindBy($class, $rowClass, $columnClass);
        $this->buildFindOneBy($class, $rowClass);
        $this->buildFind($class, $table, $rowClass);

        foreach ($table->getColumns() as $column) {
            $this->buildFindByForColumn($class, $column, $rowClass, $columnClass);
            $this->buildFindOneByForColumn($class, $column, $rowClass);
            $this->buildUpdateByForColumn($class, $column, $rowClass);
            $this->buildDeleteByForColumn($class, $column);
        }

        $this->buildCreate($class, $rowClass);
        $this->buildUpdate($class, $rowClass);
        $this->buildUpdateBy($class, $rowClass);
        $this->buildDelete($class, $rowClass);
        $this->buildDeleteBy($class);
        $this->buildNewRecord($class, $rowClass);

        return $class;
    }

    private function buildConstants(Class_ $class, Table $table): void
    {
        $constName = 'NAME';
        $class->addStmt(new Node\Stmt\ClassConst([new Node\Const_($constName, new Node\Scalar\String_($table->getName()))], Modifiers::PUBLIC));

        $columns = $table->getColumns();
        foreach ($columns as $column) {
            $constName = 'COLUMN_' . strtoupper($column->getName());
            $class->addStmt(new Node\Stmt\ClassConst([new Node\Const_($constName, new Node\Scalar\String_($column->getName()))], Modifiers::PUBLIC));
        }
    }

    private function buildGetName(Class_ $class): void
    {
        $method = $this->factory->method('getName');
        $method->makePublic();
        $method->setReturnType('string');
        $method->addStmt(new Node\Stmt\Return_(new Node\Expr\ClassConstFetch(new Node\Name('self'), 'NAME')));
        $class->addStmt($method);
    }

    private function buildGetColumns(Class_ $class, Table $table): void
    {
        $primaryIndex = $table->getPrimaryKey();

        $items = [];
        foreach ($table->getColumns() as $column) {
            $columnType = $this->getType($column, $primaryIndex?->getColumns());

            $constName = 'COLUMN_' . strtoupper($column->getName());
            $items[] = new Node\Expr\ArrayItem(
                new Node\Scalar\LNumber($columnType, ['kind' => Node\Scalar\LNumber::KIND_HEX]),
                new Node\Expr\ClassConstFetch(new Node\Name('self'), $constName)
            );
        }

        $method = $this->factory->method('getColumns');
        $method->makePublic();
        $method->setReturnType('array');
        $method->addStmt(new Node\Stmt\Return_(new Node\Expr\Array_($items)));
        $class->addStmt($method);
    }

    private function buildFind(Class_ $class, Table $table, string $rowClass): void
    {
        $primaryIndex = $table->getPrimaryKey();
        if (!$primaryIndex instanceof Index) {
            return;
        }

        $methodCall = new Node\Expr\MethodCall(new Node\Expr\Variable('this'), new Node\Identifier('doFindOneBy'), [
            new Node\Arg(new Node\Expr\Variable('condition')),
        ]);

        $method = $this->factory->method('find');
        $method->makePublic();
        $method->setReturnType('?' . $rowClass);
        $method->setDocComment($this->buildComment(['throws' => '\\' . QueryException::class]));

        foreach ($primaryIndex->getColumns() as $primaryColumn) {
            $column = $table->getColumn($primaryColumn);
            $type = $this->getTypeForColumn($column);
            $method->addParam(new Node\Param(new Node\Expr\Variable($column->getName()), null, new Node\Identifier($type)));
        }

        $method->addStmt(new Node\Stmt\Expression(new Node\Expr\Assign(new Node\Expr\Variable('condition'), new Node\Expr\StaticCall(new Node\Name('\\' . Condition::class), new Node\Identifier('withAnd')))));
        foreach ($primaryIndex->getColumns() as $primaryColumn) {
            $column = $table->getColumn($primaryColumn);
            $method->addStmt(new Node\Stmt\Expression(new Node\Expr\MethodCall(new Node\Expr\Variable('condition'), new Node\Identifier($this->getOperatorForColumn($column)), [
                new Node\Arg(new Node\Scalar\String_($column->getName())),
                new Node\Arg(new Node\Expr\Variable($column->getName()))
            ])));
        }

        $method->addStmt(new Node\Stmt\Return_($methodCall));
        $class->addStmt($method);
    }

    private function buildFindAll(Class_ $class, string $rowClass, string $columnClass): void
    {
        $methodCall = new Node\Expr\MethodCall(new Node\Expr\Variable('this'), new Node\Identifier('doFindAll'), [
            new Node\Arg(new Node\Expr\Variable('condition')),
            new Node\Arg(new Node\Expr\Variable('startIndex')),
            new Node\Arg(new Node\Expr\Variable('count')),
            new Node\Arg(new Node\Expr\Variable('sortBy')),
            new Node\Arg(new Node\Expr\Variable('sortOrder')),
        ]);

        $method = $this->factory->method('findAll');
        $method->makePublic();
        $method->setReturnType('array');
        $method->setDocComment($this->buildComment(['return' => 'array<' . $rowClass . '>', 'throws' => '\\' . QueryException::class]));
        $method->addParam(new Node\Param(new Node\Expr\Variable('condition'), new Node\Expr\ConstFetch(new Node\Name('null')), new Node\NullableType(new Node\Name('\\' . Condition::class))));
        $method->addParam(new Node\Param(new Node\Expr\Variable('startIndex'), new Node\Expr\ConstFetch(new Node\Name('null')), new Node\NullableType(new Node\Identifier('int'))));
        $method->addParam(new Node\Param(new Node\Expr\Variable('count'), new Node\Expr\ConstFetch(new Node\Name('null')), new Node\NullableType(new Node\Identifier('int'))));
        $method->addParam(new Node\Param(new Node\Expr\Variable('sortBy'), new Node\Expr\ConstFetch(new Node\Name('null')), new Node\NullableType(new Node\Name($columnClass))));
        $method->addParam(new Node\Param(new Node\Expr\Variable('sortOrder'), new Node\Expr\ConstFetch(new Node\Name('null')), new Node\NullableType(new Node\Name('\\' . OrderBy::class))));
        $method->addStmt(new Node\Stmt\Return_($methodCall));
        $class->addStmt($method);
    }

    private function buildFindBy(Class_ $class, string $rowClass, string $columnClass): void
    {
        $methodCall = new Node\Expr\MethodCall(new Node\Expr\Variable('this'), new Node\Identifier('doFindBy'), [
            new Node\Arg(new Node\Expr\Variable('condition')),
            new Node\Arg(new Node\Expr\Variable('startIndex')),
            new Node\Arg(new Node\Expr\Variable('count')),
            new Node\Arg(new Node\Expr\Variable('sortBy')),
            new Node\Arg(new Node\Expr\Variable('sortOrder')),
        ]);

        $method = $this->factory->method('findBy');
        $method->makePublic();
        $method->setReturnType('array');
        $method->setDocComment($this->buildComment(['return' => 'array<' . $rowClass . '>', 'throws' => '\\' . QueryException::class]));
        $method->addParam(new Node\Param(new Node\Expr\Variable('condition'), null, new Node\Name('\\' . Condition::class)));
        $method->addParam(new Node\Param(new Node\Expr\Variable('startIndex'), new Node\Expr\ConstFetch(new Node\Name('null')), new Node\NullableType(new Node\Identifier('int'))));
        $method->addParam(new Node\Param(new Node\Expr\Variable('count'), new Node\Expr\ConstFetch(new Node\Name('null')), new Node\NullableType(new Node\Identifier('int'))));
        $method->addParam(new Node\Param(new Node\Expr\Variable('sortBy'), new Node\Expr\ConstFetch(new Node\Name('null')), new Node\NullableType(new Node\Name($columnClass))));
        $method->addParam(new Node\Param(new Node\Expr\Variable('sortOrder'), new Node\Expr\ConstFetch(new Node\Name('null')), new Node\NullableType(new Node\Name('\\' . OrderBy::class))));
        $method->addStmt(new Node\Stmt\Return_($methodCall));
        $class->addStmt($method);
    }

    private function buildFindOneBy(Class_ $class, string $rowClass): void
    {
        $methodCall = new Node\Expr\MethodCall(new Node\Expr\Variable('this'), new Node\Identifier('doFindOneBy'), [
            new Node\Arg(new Node\Expr\Variable('condition')),
        ]);

        $method = $this->factory->method('findOneBy');
        $method->makePublic();
        $method->setReturnType('?' . $rowClass);
        $method->setDocComment($this->buildComment(['throws' => '\\' . QueryException::class]));
        $method->addParam(new Node\Param(new Node\Expr\Variable('condition'), null, new Node\Name('\\' . Condition::class)));
        $method->addStmt(new Node\Stmt\Return_($methodCall));
        $class->addStmt($method);
    }

    private function buildFindByForColumn(Class_ $class, Column $column, string $rowClass, string $columnClass): void
    {
        $methodCall = new Node\Expr\MethodCall(new Node\Expr\Variable('this'), new Node\Identifier('doFindBy'), [
            new Node\Arg(new Node\Expr\Variable('condition')),
            new Node\Arg(new Node\Expr\Variable('startIndex')),
            new Node\Arg(new Node\Expr\Variable('count')),
            new Node\Arg(new Node\Expr\Variable('sortBy')),
            new Node\Arg(new Node\Expr\Variable('sortOrder')),
        ]);

        $type = $this->getTypeForColumn($column);

        $method = $this->factory->method('findBy' . ucfirst($this->normalizeName($column->getName())));
        $method->makePublic();
        $method->setReturnType('array');
        $method->setDocComment($this->buildComment(['return' => 'array<' . $rowClass . '>', 'throws' => '\\' . QueryException::class]));
        $method->addParam(new Node\Param(new Node\Expr\Variable('value'), null, new Node\Identifier($type)));
        $method->addParam(new Node\Param(new Node\Expr\Variable('startIndex'), new Node\Expr\ConstFetch(new Node\Name('null')), new Node\NullableType(new Node\Identifier('int'))));
        $method->addParam(new Node\Param(new Node\Expr\Variable('count'), new Node\Expr\ConstFetch(new Node\Name('null')), new Node\NullableType(new Node\Identifier('int'))));
        $method->addParam(new Node\Param(new Node\Expr\Variable('sortBy'), new Node\Expr\ConstFetch(new Node\Name('null')), new Node\NullableType(new Node\Name($columnClass))));
        $method->addParam(new Node\Param(new Node\Expr\Variable('sortOrder'), new Node\Expr\ConstFetch(new Node\Name('null')), new Node\NullableType(new Node\Name('\\' . OrderBy::class))));
        $method->addStmt(new Node\Stmt\Expression(new Node\Expr\Assign(new Node\Expr\Variable('condition'), new Node\Expr\StaticCall(new Node\Name('\\' . Condition::class), new Node\Identifier('withAnd')))));
        $method->addStmt(new Node\Stmt\Expression(new Node\Expr\MethodCall(new Node\Expr\Variable('condition'), new Node\Identifier($this->getOperatorForColumn($column)), [
            new Node\Arg(new Node\Scalar\String_($column->getName())),
            new Node\Arg(new Node\Expr\Variable('value'))
        ])));
        $method->addStmt(new Node\Stmt\Return_($methodCall));
        $class->addStmt($method);
    }

    private function buildFindOneByForColumn(Class_ $class, Column $column, string $rowClass): void
    {
        $methodCall = new Node\Expr\MethodCall(new Node\Expr\Variable('this'), new Node\Identifier('doFindOneBy'), [
            new Node\Arg(new Node\Expr\Variable('condition')),
        ]);

        $type = $this->getTypeForColumn($column);

        $method = $this->factory->method('findOneBy' . ucfirst($this->normalizeName($column->getName())));
        $method->makePublic();
        $method->setReturnType('?' . $rowClass);
        $method->setDocComment($this->buildComment(['throws' => '\\' . QueryException::class]));
        $method->addParam(new Node\Param(new Node\Expr\Variable('value'), null, new Node\Identifier($type)));
        $method->addStmt(new Node\Stmt\Expression(new Node\Expr\Assign(new Node\Expr\Variable('condition'), new Node\Expr\StaticCall(new Node\Name('\\' . Condition::class), new Node\Identifier('withAnd')))));
        $method->addStmt(new Node\Stmt\Expression(new Node\Expr\MethodCall(new Node\Expr\Variable('condition'), new Node\Identifier($this->getOperatorForColumn($column)), [
            new Node\Arg(new Node\Scalar\String_($column->getName())),
            new Node\Arg(new Node\Expr\Variable('value'))
        ])));
        $method->addStmt(new Node\Stmt\Return_($methodCall));
        $class->addStmt($method);
    }

    private function buildUpdateByForColumn(Class_ $class, Column $column, string $rowClass): void
    {
        $methodCall = new Node\Expr\MethodCall(new Node\Expr\Variable('this'), new Node\Identifier('doUpdateBy'), [
            new Node\Arg(new Node\Expr\Variable('condition')),
            new Node\Arg(new Node\Expr\MethodCall(new Node\Expr\Variable('record'), new Node\Identifier('toRecord'))),
        ]);

        $type = $this->getTypeForColumn($column);

        $method = $this->factory->method('updateBy' . ucfirst($this->normalizeName($column->getName())));
        $method->makePublic();
        $method->setReturnType(new Node\Name('int'));
        $method->setDocComment($this->buildComment(['throws' => '\\' . ManipulationException::class]));
        $method->addParam(new Node\Param(new Node\Expr\Variable('value'), null, new Node\Identifier($type)));
        $method->addParam(new Node\Param(new Node\Expr\Variable('record'), null, new Node\Identifier($rowClass)));
        $method->addStmt(new Node\Stmt\Expression(new Node\Expr\Assign(new Node\Expr\Variable('condition'), new Node\Expr\StaticCall(new Node\Name('\\' . Condition::class), new Node\Identifier('withAnd')))));
        $method->addStmt(new Node\Stmt\Expression(new Node\Expr\MethodCall(new Node\Expr\Variable('condition'), new Node\Identifier($this->getOperatorForColumn($column)), [
            new Node\Arg(new Node\Scalar\String_($column->getName())),
            new Node\Arg(new Node\Expr\Variable('value'))
        ])));
        $method->addStmt(new Node\Stmt\Return_($methodCall));
        $class->addStmt($method);
    }

    private function buildDeleteByForColumn(Class_ $class, Column $column): void
    {
        $methodCall = new Node\Expr\MethodCall(new Node\Expr\Variable('this'), new Node\Identifier('doDeleteBy'), [
            new Node\Arg(new Node\Expr\Variable('condition')),
        ]);

        $type = $this->getTypeForColumn($column);

        $method = $this->factory->method('deleteBy' . ucfirst($this->normalizeName($column->getName())));
        $method->makePublic();
        $method->setReturnType(new Node\Name('int'));
        $method->setDocComment($this->buildComment(['throws' => '\\' . ManipulationException::class]));
        $method->addParam(new Node\Param(new Node\Expr\Variable('value'), null, new Node\Identifier($type)));
        $method->addStmt(new Node\Stmt\Expression(new Node\Expr\Assign(new Node\Expr\Variable('condition'), new Node\Expr\StaticCall(new Node\Name('\\' . Condition::class), new Node\Identifier('withAnd')))));
        $method->addStmt(new Node\Stmt\Expression(new Node\Expr\MethodCall(new Node\Expr\Variable('condition'), new Node\Identifier($this->getOperatorForColumn($column)), [
            new Node\Arg(new Node\Scalar\String_($column->getName())),
            new Node\Arg(new Node\Expr\Variable('value'))
        ])));
        $method->addStmt(new Node\Stmt\Return_($methodCall));
        $class->addStmt($method);
    }

    private function buildCreate(Class_ $class, string $rowClass): void
    {
        $methodCall = new Node\Expr\MethodCall(new Node\Expr\Variable('this'), new Node\Identifier('doCreate'), [
            new Node\Arg(new Node\Expr\MethodCall(new Node\Expr\Variable('record'), new Node\Identifier('toRecord'))),
        ]);

        $method = $this->factory->method('create');
        $method->makePublic();
        $method->setReturnType(new Node\Name('int'));
        $method->setDocComment($this->buildComment(['throws' => '\\' . ManipulationException::class]));
        $method->addParam(new Node\Param(new Node\Expr\Variable('record'), null, new Node\Identifier($rowClass)));
        $method->addStmt(new Node\Stmt\Return_($methodCall));
        $class->addStmt($method);
    }

    private function buildUpdate(Class_ $class, string $rowClass): void
    {
        $methodCall = new Node\Expr\MethodCall(new Node\Expr\Variable('this'), new Node\Identifier('doUpdate'), [
            new Node\Arg(new Node\Expr\MethodCall(new Node\Expr\Variable('record'), new Node\Identifier('toRecord'))),
        ]);

        $method = $this->factory->method('update');
        $method->makePublic();
        $method->setReturnType(new Node\Name('int'));
        $method->setDocComment($this->buildComment(['throws' => '\\' . ManipulationException::class]));
        $method->addParam(new Node\Param(new Node\Expr\Variable('record'), null, new Node\Identifier($rowClass)));
        $method->addStmt(new Node\Stmt\Return_($methodCall));
        $class->addStmt($method);
    }

    private function buildUpdateBy(Class_ $class, string $rowClass): void
    {
        $methodCall = new Node\Expr\MethodCall(new Node\Expr\Variable('this'), new Node\Identifier('doUpdateBy'), [
            new Node\Arg(new Node\Expr\Variable('condition')),
            new Node\Arg(new Node\Expr\MethodCall(new Node\Expr\Variable('record'), new Node\Identifier('toRecord'))),
        ]);

        $method = $this->factory->method('updateBy');
        $method->makePublic();
        $method->setReturnType(new Node\Name('int'));
        $method->setDocComment($this->buildComment(['throws' => '\\' . ManipulationException::class]));
        $method->addParam(new Node\Param(new Node\Expr\Variable('condition'), null, new Node\Identifier('\\' . Condition::class)));
        $method->addParam(new Node\Param(new Node\Expr\Variable('record'), null, new Node\Identifier($rowClass)));
        $method->addStmt(new Node\Stmt\Return_($methodCall));
        $class->addStmt($method);
    }

    private function buildDelete(Class_ $class, string $rowClass): void
    {
        $methodCall = new Node\Expr\MethodCall(new Node\Expr\Variable('this'), new Node\Identifier('doDelete'), [
            new Node\Arg(new Node\Expr\MethodCall(new Node\Expr\Variable('record'), new Node\Identifier('toRecord'))),
        ]);

        $method = $this->factory->method('delete');
        $method->makePublic();
        $method->setReturnType(new Node\Name('int'));
        $method->setDocComment($this->buildComment(['throws' => '\\' . ManipulationException::class]));
        $method->addParam(new Node\Param(new Node\Expr\Variable('record'), null, new Node\Identifier($rowClass)));
        $method->addStmt(new Node\Stmt\Return_($methodCall));
        $class->addStmt($method);
    }

    private function buildDeleteBy(Class_ $class): void
    {
        $methodCall = new Node\Expr\MethodCall(new Node\Expr\Variable('this'), new Node\Identifier('doDeleteBy'), [
            new Node\Arg(new Node\Expr\Variable('condition')),
        ]);

        $method = $this->factory->method('deleteBy');
        $method->makePublic();
        $method->setReturnType(new Node\Name('int'));
        $method->setDocComment($this->buildComment(['throws' => '\\' . ManipulationException::class]));
        $method->addParam(new Node\Param(new Node\Expr\Variable('condition'), null, new Node\Identifier('\\' . Condition::class)));
        $method->addStmt(new Node\Stmt\Return_($methodCall));
        $class->addStmt($method);
    }

    private function buildNewRecord(Class_ $class, string $rowClass): void
    {
        $method = $this->factory->method('newRecord');
        $method->makeProtected();
        $method->setReturnType(new Node\Name($rowClass));
        $method->setDocComment($this->buildComment(['param' => 'array<string, mixed> $row']));
        $method->addParam(new Node\Param(new Node\Expr\Variable('row'), null, new Node\Name('array')));
        $method->addStmt(new Node\Stmt\Return_(new Node\Expr\StaticCall(new Node\Name($rowClass), new Node\Identifier('from'), [
            new Node\Arg(new Node\Expr\Variable('row'))
        ])));
        $class->addStmt($method);
    }

    private function generateColumn(string $className, string $repositoryClass, Table $table): Enum_
    {
        $tableClass = $this->namespace !== null ? '\\' . $this->namespace . '\\' . $repositoryClass : '\\' . $repositoryClass;

        $enum = $this->factory->enum($className);
        $enum->implement('\\' . ColumnInterface::class);
        $enum->setScalarType('string');

        $reserved = ['CLASS'];
        $columns = $table->getColumns();
        foreach ($columns as $column) {
            $caseName = strtoupper($column->getName());
            $constName = 'COLUMN_' . strtoupper($column->getName());

            if (in_array($caseName, $reserved)) {
                $caseName .= '_';
            }

            $enum->addStmt(new Node\Stmt\EnumCase(new Node\Identifier($caseName), new Node\Expr\ClassConstFetch(new Node\Name($tableClass), new Node\Identifier($constName))));
        }

        return $enum;
    }

    private function normalizeName(string $name): string
    {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $name)));
    }

    private function buildComment(array $tags): string
    {
        $lines = [];
        foreach ($tags as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $val) {
                    $lines[] = ' * @' . $key . ' ' . trim($val);
                }
            } else {
                $lines[] = ' * @' . $key . ' ' . trim($value);
            }
        }

        if (empty($lines)) {
            return '';
        }

        return '/**' . "\n" . implode("\n", $lines) . "\n" . ' */';
    }

    private function prettyPrint(Declaration $declaration): string
    {
        if ($this->namespace !== null) {
            $namespace = $this->factory->namespace($this->namespace);
            $namespace->addStmt($declaration);

            return $this->printer->prettyPrint([$namespace->getNode()]);
        } else {
            return $this->printer->prettyPrint([$declaration->getNode()]);
        }
    }

    private function getTypeForColumn(Column $column): string
    {
        $type = $column->getType();

        if ($type instanceof Types\BigIntType) {
            return 'string';
        } elseif ($type instanceof Types\BinaryType) {
            return 'mixed';
        } elseif ($type instanceof Types\BlobType) {
            return 'mixed';
        } elseif ($type instanceof Types\BooleanType) {
            return 'bool';
        } elseif ($type instanceof Types\DateType) {
            return '\\' . LocalDate::class;
        } elseif ($type instanceof Types\DateTimeType) {
            return '\\' . LocalDateTime::class;
        } elseif ($type instanceof Types\DateIntervalType) {
            return '\\' . Period::class;
        } elseif ($type instanceof Types\DecimalType) {
            return 'string';
        } elseif ($type instanceof Types\FloatType) {
            return 'float';
        } elseif ($type instanceof Types\GuidType) {
            return 'string';
        } elseif ($type instanceof Types\IntegerType) {
            return 'int';
        } elseif ($type instanceof Types\JsonType) {
            return 'mixed';
        } elseif ($type instanceof Types\SimpleArrayType) {
            return 'string';
        } elseif ($type instanceof Types\SmallIntType) {
            return 'int';
        } elseif ($type instanceof Types\StringType) {
            return 'string';
        } elseif ($type instanceof Types\TextType) {
            return 'string';
        } elseif ($type instanceof Types\TimeType) {
            return '\\' . LocalTime::class;
        } else {
            return 'string';
        }
    }

    private function getValidatorForType(Column $column): ?string
    {
        $type = $column->getType();

        if ($type instanceof Types\BigIntType) {
            return 'is_string';
        } elseif ($type instanceof Types\BinaryType) {
            return null;
        } elseif ($type instanceof Types\BlobType) {
            return null;
        } elseif ($type instanceof Types\BooleanType) {
            return 'is_bool';
        } elseif ($type instanceof Types\DateType) {
            return '\\' . \DateTimeInterface::class;
        } elseif ($type instanceof Types\DateTimeType) {
            return '\\' . \DateTimeInterface::class;
        } elseif ($type instanceof Types\DateIntervalType) {
            return '\\' . \DateInterval::class;
        } elseif ($type instanceof Types\DecimalType) {
            return 'is_string';
        } elseif ($type instanceof Types\FloatType) {
            return 'is_float';
        } elseif ($type instanceof Types\GuidType) {
            return 'is_string';
        } elseif ($type instanceof Types\IntegerType) {
            return 'is_int';
        } elseif ($type instanceof Types\JsonType) {
            return null;
        } elseif ($type instanceof Types\SimpleArrayType) {
            return 'is_string';
        } elseif ($type instanceof Types\SmallIntType) {
            return 'is_int';
        } elseif ($type instanceof Types\StringType) {
            return 'is_string';
        } elseif ($type instanceof Types\TextType) {
            return 'is_string';
        } elseif ($type instanceof Types\TimeType) {
            return '\\' . \DateTimeInterface::class;
        } else {
            return 'is_string';
        }
    }

    private function getOperatorForColumn(Column $column): string
    {
        $type = $column->getType();

        if ($type instanceof Types\StringType) {
            return 'like';
        } elseif ($type instanceof Types\TextType) {
            return 'like';
        }

        return 'equals';
    }

    private function getType(Column $column, ?array $primaryColumns): int
    {
        $type = 0;

        $dbalType = $column->getType();
        if ($dbalType instanceof Types\IntegerType || $dbalType instanceof Types\FloatType) {
            $type+= $column->getPrecision();
        } else {
            $type+= $column->getLength();
        }

        $typeName = array_search($dbalType::class, Types\Type::getTypesMap());
        if ($typeName === false) {
            throw new GeneratorException('Could not determine type name');
        }

        $type = $type | TypeMapper::getTypeByDoctrineType($typeName);

        if ($primaryColumns !== null && in_array($column->getName(), $primaryColumns)) {
            $type = $type | TableInterface::PRIMARY_KEY;
        }

        if ($column->getAutoincrement()) {
            $type = $type | TableInterface::AUTO_INCREMENT;
        }

        if (!$column->getNotnull()) {
            $type = $type | TableInterface::IS_NULL;
        }

        return $type;
    }
}
