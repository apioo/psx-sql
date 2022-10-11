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

namespace PSX\Sql\Generator;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types;
use PhpParser\Builder;
use PhpParser\BuilderFactory;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\PrettyPrinter;
use PSX\Record\Record;
use PSX\Sql\Condition;
use PSX\Sql\Exception\ManipulationException;
use PSX\Sql\Exception\QueryException;
use PSX\Sql\Fields;
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
        $schemaManager = $this->connection->getSchemaManager();
        $tableNames = $schemaManager->listTableNames();
        
        foreach ($tableNames as $tableName) {
            $table = $schemaManager->listTableDetails($tableName);

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

            $class = $this->generateModel($modelClassName, $table);
            yield $modelClassName => $this->prettyPrint($class);

            $class = $this->generateRepository($repositoryClassName, $modelClassName, $table);
            yield $repositoryClassName => $this->prettyPrint($class);
        }
    }

    private function generateModel(string $className, Table $table): Builder\Class_
    {
        $class = $this->factory->class($className);
        $class->extend('\\' . Record::class);

        $serialize = [];
        $columns = $table->getColumns();
        foreach ($columns as $column) {
            $name = lcfirst($this->normalizeName($column->getName()));
            $type = $this->getTypeForColumn($column);

            $serialize[$name] = $column->getName();

            // setter
            $param = $this->factory->param($name);
            if (!empty($type)) {
                if ($type !== 'mixed') {
                    $param->setType(new Node\NullableType($type));
                } else {
                    $param->setType($type);
                }
            }

            $setter = $this->factory->method('set' . ucfirst($name));
            $setter->setReturnType('void');
            $setter->makePublic();
            $setter->addParam($param);
            $setter->addStmt(new Node\Expr\MethodCall(new Node\Expr\Variable('this'), new Node\Identifier('setProperty'), [
                new Node\Arg(new Node\Scalar\String_($column->getName())),
                new Node\Arg(new Node\Expr\Variable($name)),
            ]));
            $class->addStmt($setter);

            // getter
            $getter = $this->factory->method('get' . ucfirst($name));
            if (!empty($type)) {
                if ($type !== 'mixed') {
                    $getter->setReturnType(new Node\NullableType($type));
                } else {
                    $getter->setReturnType($type);
                }
            } else {
                $getter->setReturnType('void');
            }
            $getter->makePublic();
            $getter->addStmt(new Node\Stmt\Return_(
                new Node\Expr\MethodCall(new Node\Expr\Variable('this'), new Node\Identifier('getProperty'), [
                    new Node\Arg(new Node\Scalar\String_($column->getName())),
                ])
            ));

            $class->addStmt($getter);
        }

        return $class;
    }

    private function generateRepository(string $className, string $rowClass, Table $table): Builder\Class_
    {
        if ($this->namespace !== null) {
            $rowClass = '\\' . $this->namespace . '\\' . $rowClass;
        } else {
            $rowClass = '\\' . $rowClass;
        }

        $class = $this->factory->class($className);
        $class->extend('\\' . TableAbstract::class);
        $class->setDocComment($this->buildComment(['extends' => '\\' . TableAbstract::class . '<' . $rowClass . '>']));

        $this->buildConstants($class, $table);
        $this->buildGetName($class, $table);
        $this->buildGetColumns($class, $table);
        $this->buildFindAll($class, $rowClass);
        $this->buildFindBy($class, $rowClass);
        $this->buildFindOneBy($class, $rowClass);
        $this->buildFind($class, $table, $rowClass);

        foreach ($table->getColumns() as $column) {
            $this->buildFindByForColumn($class, $column, $rowClass);
            $this->buildFindOneByForColumn($class, $column, $rowClass);
        }

        $this->buildCreate($class, $rowClass);
        $this->buildUpdate($class, $rowClass);
        $this->buildDelete($class, $rowClass);
        $this->buildNewRecord($class, $rowClass);

        return $class;
    }

    private function buildConstants(Builder\Class_ $class, Table $table)
    {
        $constName = 'NAME';
        $class->addStmt(new Node\Stmt\ClassConst([new Node\Const_($constName, new Node\Scalar\String_($table->getName()))], Class_::MODIFIER_PUBLIC));

        $columns = $table->getColumns();
        foreach ($columns as $column) {
            $constName = 'COLUMN_' . strtoupper($column->getName());
            $class->addStmt(new Node\Stmt\ClassConst([new Node\Const_($constName, new Node\Scalar\String_($column->getName()))], Class_::MODIFIER_PUBLIC));
        }
    }

    private function buildGetName(Builder\Class_ $class, Table $table)
    {
        $method = $this->factory->method('getName');
        $method->makePublic();
        $method->setReturnType('string');
        $method->addStmt(new Node\Stmt\Return_(new Node\Expr\ClassConstFetch(new Node\Name('self'), 'NAME')));
        $class->addStmt($method);
    }

    private function buildGetColumns(Builder\Class_ $class, Table $table)
    {
        $primaryColumns = $table->getPrimaryKeyColumns();

        $items = [];
        foreach ($table->getColumns() as $column) {
            $columnType = $this->getType($column, $primaryColumns);

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

    private function buildFind(Builder\Class_ $class, Table $table, string $rowClass)
    {
        $primaryColumns = $table->getPrimaryKeyColumns();
        $methodCall = new Node\Expr\MethodCall(new Node\Expr\Variable('this'), new Node\Identifier('doFindOneBy'), [
            new Node\Arg(new Node\Expr\Variable('condition')),
        ]);

        $method = $this->factory->method('find');
        $method->makePublic();
        $method->setReturnType(new Node\NullableType($rowClass));
        $method->setDocComment($this->buildComment(['throws' => '\\' . QueryException::class]));

        foreach ($primaryColumns as $primaryColumn) {
            $column = $table->getColumn($primaryColumn);
            if ($column instanceof Column) {
                $type = $this->getTypeForColumn($column);
                $method->addParam(new Node\Param(new Node\Expr\Variable($column->getName()), null, new Node\Identifier($type)));
            }
        }

        $method->addStmt(new Node\Stmt\Expression(new Node\Expr\Assign(new Node\Expr\Variable('condition'), new Node\Expr\New_(new Node\Name('\\' . Condition::class)))));
        foreach ($primaryColumns as $primaryColumn) {
            $column = $table->getColumn($primaryColumn);
            if ($column instanceof Column) {
                $method->addStmt(new Node\Stmt\Expression(new Node\Expr\MethodCall(new Node\Expr\Variable('condition'), new Node\Identifier($this->getOperatorForColumn($column)), [
                    new Node\Arg(new Node\Scalar\String_($column->getName())),
                    new Node\Arg(new Node\Expr\Variable($column->getName()))
                ])));
            }
        }

        $method->addStmt(new Node\Stmt\Return_($methodCall));
        $class->addStmt($method);
    }

    private function buildFindAll(Builder\Class_ $class, string $rowClass)
    {
        $methodCall = new Node\Expr\MethodCall(new Node\Expr\Variable('this'), new Node\Identifier('doFindAll'), [
            new Node\Arg(new Node\Expr\Variable('condition')),
            new Node\Arg(new Node\Expr\Variable('startIndex')),
            new Node\Arg(new Node\Expr\Variable('count')),
            new Node\Arg(new Node\Expr\Variable('sortBy')),
            new Node\Arg(new Node\Expr\Variable('sortOrder')),
            new Node\Arg(new Node\Expr\Variable('fields')),
        ]);

        $method = $this->factory->method('findAll');
        $method->makePublic();
        $method->setReturnType('array');
        $method->setDocComment($this->buildComment(['return' => 'array<' . $rowClass . '>', 'throws' => '\\' . QueryException::class]));
        $method->addParam(new Node\Param(new Node\Expr\Variable('condition'), new Node\Expr\ConstFetch(new Node\Name('null')), new Node\NullableType(new Node\Name('\\' . Condition::class))));
        $method->addParam(new Node\Param(new Node\Expr\Variable('startIndex'), new Node\Expr\ConstFetch(new Node\Name('null')), new Node\NullableType(new Node\Identifier('int'))));
        $method->addParam(new Node\Param(new Node\Expr\Variable('count'), new Node\Expr\ConstFetch(new Node\Name('null')), new Node\NullableType(new Node\Identifier('int'))));
        $method->addParam(new Node\Param(new Node\Expr\Variable('sortBy'), new Node\Expr\ConstFetch(new Node\Name('null')), new Node\NullableType(new Node\Identifier('string'))));
        $method->addParam(new Node\Param(new Node\Expr\Variable('sortOrder'), new Node\Expr\ConstFetch(new Node\Name('null')), new Node\NullableType(new Node\Identifier('int'))));
        $method->addParam(new Node\Param(new Node\Expr\Variable('fields'), new Node\Expr\ConstFetch(new Node\Name('null')), new Node\NullableType(new Node\Name('\\' . Fields::class))));
        $method->addStmt(new Node\Stmt\Return_($methodCall));
        $class->addStmt($method);
    }

    private function buildFindBy(Builder\Class_ $class, string $rowClass)
    {
        $methodCall = new Node\Expr\MethodCall(new Node\Expr\Variable('this'), new Node\Identifier('doFindBy'), [
            new Node\Arg(new Node\Expr\Variable('condition')),
            new Node\Arg(new Node\Expr\Variable('startIndex')),
            new Node\Arg(new Node\Expr\Variable('count')),
            new Node\Arg(new Node\Expr\Variable('sortBy')),
            new Node\Arg(new Node\Expr\Variable('sortOrder')),
            new Node\Arg(new Node\Expr\Variable('fields')),
        ]);

        $method = $this->factory->method('findBy');
        $method->makePublic();
        $method->setReturnType('array');
        $method->setDocComment($this->buildComment(['return' => 'array<' . $rowClass . '>', 'throws' => '\\' . QueryException::class]));
        $method->addParam(new Node\Param(new Node\Expr\Variable('condition'), null, new Node\Name('\\' . Condition::class)));
        $method->addParam(new Node\Param(new Node\Expr\Variable('startIndex'), new Node\Expr\ConstFetch(new Node\Name('null')), new Node\NullableType(new Node\Identifier('int'))));
        $method->addParam(new Node\Param(new Node\Expr\Variable('count'), new Node\Expr\ConstFetch(new Node\Name('null')), new Node\NullableType(new Node\Identifier('int'))));
        $method->addParam(new Node\Param(new Node\Expr\Variable('sortBy'), new Node\Expr\ConstFetch(new Node\Name('null')), new Node\NullableType(new Node\Identifier('string'))));
        $method->addParam(new Node\Param(new Node\Expr\Variable('sortOrder'), new Node\Expr\ConstFetch(new Node\Name('null')), new Node\NullableType(new Node\Identifier('int'))));
        $method->addParam(new Node\Param(new Node\Expr\Variable('fields'), new Node\Expr\ConstFetch(new Node\Name('null')), new Node\NullableType(new Node\Name('\\' . Fields::class))));
        $method->addStmt(new Node\Stmt\Return_($methodCall));
        $class->addStmt($method);
    }

    private function buildFindOneBy(Builder\Class_ $class, string $rowClass)
    {
        $methodCall = new Node\Expr\MethodCall(new Node\Expr\Variable('this'), new Node\Identifier('doFindOneBy'), [
            new Node\Arg(new Node\Expr\Variable('condition')),
            new Node\Arg(new Node\Expr\Variable('fields')),
        ]);

        $method = $this->factory->method('findOneBy');
        $method->makePublic();
        $method->setReturnType(new Node\NullableType($rowClass));
        $method->setDocComment($this->buildComment(['throws' => '\\' . QueryException::class]));
        $method->addParam(new Node\Param(new Node\Expr\Variable('condition'), null, new Node\Name('\\' . Condition::class)));
        $method->addParam(new Node\Param(new Node\Expr\Variable('fields'), new Node\Expr\ConstFetch(new Node\Name('null')), new Node\NullableType(new Node\Name('\\' . Fields::class))));
        $method->addStmt(new Node\Stmt\Return_($methodCall));
        $class->addStmt($method);
    }

    private function buildFindByForColumn(Builder\Class_ $class, Column $column, string $rowClass)
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
        $method->addParam(new Node\Param(new Node\Expr\Variable('sortBy'), new Node\Expr\ConstFetch(new Node\Name('null')), new Node\NullableType(new Node\Identifier('string'))));
        $method->addParam(new Node\Param(new Node\Expr\Variable('sortOrder'), new Node\Expr\ConstFetch(new Node\Name('null')), new Node\NullableType(new Node\Identifier('int'))));
        $method->addStmt(new Node\Stmt\Expression(new Node\Expr\Assign(new Node\Expr\Variable('condition'), new Node\Expr\New_(new Node\Name('\\' . Condition::class)))));
        $method->addStmt(new Node\Stmt\Expression(new Node\Expr\MethodCall(new Node\Expr\Variable('condition'), new Node\Identifier($this->getOperatorForColumn($column)), [
            new Node\Arg(new Node\Scalar\String_($column->getName())),
            new Node\Arg(new Node\Expr\Variable('value'))
        ])));
        $method->addStmt(new Node\Stmt\Return_($methodCall));
        $class->addStmt($method);
    }

    private function buildFindOneByForColumn(Builder\Class_ $class, Column $column, string $rowClass)
    {
        $methodCall = new Node\Expr\MethodCall(new Node\Expr\Variable('this'), new Node\Identifier('doFindOneBy'), [
            new Node\Arg(new Node\Expr\Variable('condition')),
        ]);

        $type = $this->getTypeForColumn($column);

        $method = $this->factory->method('findOneBy' . ucfirst($this->normalizeName($column->getName())));
        $method->makePublic();
        $method->setReturnType(new Node\NullableType($rowClass));
        $method->setDocComment($this->buildComment(['throws' => '\\' . QueryException::class]));
        $method->addParam(new Node\Param(new Node\Expr\Variable('value'), null, new Node\Identifier($type)));
        $method->addStmt(new Node\Stmt\Expression(new Node\Expr\Assign(new Node\Expr\Variable('condition'), new Node\Expr\New_(new Node\Name('\\' . Condition::class)))));
        $method->addStmt(new Node\Stmt\Expression(new Node\Expr\MethodCall(new Node\Expr\Variable('condition'), new Node\Identifier($this->getOperatorForColumn($column)), [
            new Node\Arg(new Node\Scalar\String_($column->getName())),
            new Node\Arg(new Node\Expr\Variable('value'))
        ])));
        $method->addStmt(new Node\Stmt\Return_($methodCall));
        $class->addStmt($method);
    }

    private function buildCreate(Builder\Class_ $class, string $rowClass)
    {
        $methodCall = new Node\Expr\MethodCall(new Node\Expr\Variable('this'), new Node\Identifier('doCreate'), [
            new Node\Arg(new Node\Expr\Variable('record')),
        ]);

        $method = $this->factory->method('create');
        $method->makePublic();
        $method->setReturnType(new Node\Name('int'));
        $method->setDocComment($this->buildComment(['throws' => '\\' . ManipulationException::class]));
        $method->addParam(new Node\Param(new Node\Expr\Variable('record'), null, new Node\Identifier($rowClass)));
        $method->addStmt(new Node\Stmt\Return_($methodCall));
        $class->addStmt($method);
    }

    private function buildUpdate(Builder\Class_ $class, string $rowClass)
    {
        $methodCall = new Node\Expr\MethodCall(new Node\Expr\Variable('this'), new Node\Identifier('doUpdate'), [
            new Node\Arg(new Node\Expr\Variable('record')),
        ]);

        $method = $this->factory->method('update');
        $method->makePublic();
        $method->setReturnType(new Node\Name('int'));
        $method->setDocComment($this->buildComment(['throws' => '\\' . ManipulationException::class]));
        $method->addParam(new Node\Param(new Node\Expr\Variable('record'), null, new Node\Identifier($rowClass)));
        $method->addStmt(new Node\Stmt\Return_($methodCall));
        $class->addStmt($method);
    }

    private function buildDelete(Builder\Class_ $class, string $rowClass)
    {
        $methodCall = new Node\Expr\MethodCall(new Node\Expr\Variable('this'), new Node\Identifier('doDelete'), [
            new Node\Arg(new Node\Expr\Variable('record')),
        ]);

        $method = $this->factory->method('delete');
        $method->makePublic();
        $method->setReturnType(new Node\Name('int'));
        $method->setDocComment($this->buildComment(['throws' => '\\' . ManipulationException::class]));
        $method->addParam(new Node\Param(new Node\Expr\Variable('record'), null, new Node\Identifier($rowClass)));
        $method->addStmt(new Node\Stmt\Return_($methodCall));
        $class->addStmt($method);
    }

    private function buildNewRecord(Builder\Class_ $class, string $rowClass)
    {
        $method = $this->factory->method('newRecord');
        $method->makeProtected();
        $method->setReturnType(new Node\Name($rowClass));
        $method->setDocComment($this->buildComment(['param' => 'array<string, mixed> $row']));
        $method->addParam(new Node\Param(new Node\Expr\Variable('row'), null, 'array'));
        $method->addStmt(new Node\Stmt\Return_(new Node\Expr\New_(new Node\Name($rowClass), [
            new Node\Arg(new Node\Expr\Variable('row'))
        ])));
        $class->addStmt($method);
    }

    private function normalizeName(string $name): string
    {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $name)));
    }

    private function buildComment(array $tags, ?string $comment = null): string
    {
        $lines = [];
        if (!empty($comment)) {
            $lines[] = ' * ' . $comment;
        }

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

    private function prettyPrint($class)
    {
        if ($this->namespace !== null) {
            $namespace = $this->factory->namespace($this->namespace);
            $namespace->addStmt($class);

            return $this->printer->prettyPrint([$namespace->getNode()]);
        } else {
            return $this->printer->prettyPrint([$class->getNode()]);
        }
    }

    private function getTypeForColumn(Column $column): string
    {
        $type = $column->getType();

        if ($type instanceof Types\ArrayType) {
            return 'mixed';
        } elseif ($type instanceof Types\BigIntType) {
            return 'int';
        } elseif ($type instanceof Types\BinaryType) {
            return 'mixed';
        } elseif ($type instanceof Types\BlobType) {
            return 'mixed';
        } elseif ($type instanceof Types\BooleanType) {
            return 'bool';
        } elseif ($type instanceof Types\DateType) {
            return '\\' . \DateTime::class;
        } elseif ($type instanceof Types\DateTimeType) {
            return '\\' . \DateTime::class;
        } elseif ($type instanceof Types\DateTimeTzType) {
            return '\\' . \DateTime::class;
        } elseif ($type instanceof Types\DecimalType) {
            return 'float';
        } elseif ($type instanceof Types\FloatType) {
            return 'float';
        } elseif ($type instanceof Types\GuidType) {
            return 'string';
        } elseif ($type instanceof Types\IntegerType) {
            return 'int';
        } elseif ($type instanceof Types\JsonType) {
            return 'mixed';
        } elseif ($type instanceof Types\ObjectType) {
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
            return '\\' . \DateTime::class;
        } else {
            return 'string';
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

    private function getType(Column $column, ?array $primaryColumns)
    {
        $type = 0;

        $dbalType = $column->getType();
        if ($dbalType instanceof Types\IntegerType || $dbalType instanceof Types\FloatType) {
            $type+= $column->getPrecision();
        } else {
            $type+= $column->getLength();
        }

        $type = $type | TypeMapper::getTypeByDoctrineType($dbalType->getName());

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
