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

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types;
use PhpParser\BuilderFactory;
use PhpParser\Node\Stmt\Class_;
use PhpParser\PrettyPrinter;
use PhpParser\Node;
use PSX\DateTime\Date;
use PSX\DateTime\DateTime;
use PSX\DateTime\Time;
use PSX\Sql\Condition;
use PSX\Sql\TableAbstract;
use PSX\Sql\TableInterface;
use PSX\Sql\TypeMapper;

/**
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    http://phpsx.org
 */
class Generator
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var string|null
     */
    private $namespace;

    /**
     * @var string|null
     */
    private $prefix;

    /**
     * @var BuilderFactory
     */
    private $factory;

    /**
     * @var PrettyPrinter\Standard
     */
    private $printer;

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

    private function generateModel(string $className, Table $table)
    {
        $class = $this->factory->class($className);
        $class->implement('\\JsonSerializable');

        $serialize = [];
        $columns = $table->getColumns();
        foreach ($columns as $column) {
            $name = lcfirst($this->normalizeName($column->getName()));
            $type = $this->getTypeForColumn($column);

            $serialize[$name] = $column->getName();

            // property
            $prop = $this->factory->property($name);
            $prop->makePrivate();
            $prop->setType($type !== null ? '?' . $type : 'mixed');
            $class->addStmt($prop);

            // setter
            $param = $this->factory->param($name);
            if (!empty($type)) {
                $param->setType(new Node\NullableType($type));
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
                $getter->setReturnType(new Node\NullableType($type));
            } else {
                $setter->setReturnType('void');
            }
            $getter->makePublic();
            $getter->addStmt(new Node\Stmt\Return_(
                new Node\Expr\PropertyFetch(new Node\Expr\Variable('this'), $name)
            ));
            $class->addStmt($getter);
        }

        $this->buildJsonSerialize($class, $serialize);

        return $class;
    }


    private function buildJsonSerialize(\PhpParser\Builder\Class_ $class, array $properties)
    {
        if (empty($properties)) {
            return;
        }

        $items = [];
        foreach ($properties as $name => $key) {
            $items[] = new Node\Expr\ArrayItem(new Node\Expr\PropertyFetch(new Node\Expr\Variable('this'), $name), new Node\Scalar\String_($key));
        }

        $closure = new Node\Expr\Closure([
            'static' => true,
            'params' => [new Node\Expr\Variable('value')],
            'returnType' => 'bool',
            'stmts' => [
                new Node\Stmt\Return_(new Node\Expr\BinaryOp\NotIdentical(new Node\Expr\Variable('value'), new Node\Expr\ConstFetch(new Node\Name('null'))))
            ],
        ]);

        $filter = new Node\Expr\FuncCall(new Node\Name('array_filter'), [
            new Node\Arg(new Node\Expr\Array_($items)),
            new Node\Arg($closure)
        ]);

        $serialize = $this->factory->method('jsonSerialize');
        $serialize->makePublic();
        $serialize->addStmt(new Node\Stmt\Return_($filter));

        $class->addStmt($serialize);
    }

    private function generateRepository(string $className, string $rowClass, Table $table)
    {
        $class = $this->factory->class($className);
        $class->extend('\\' . TableAbstract::class);

        $columns = $table->getColumns();

        // constants
        foreach ($columns as $column) {
            $constName = 'COLUMN_' . strtoupper($column->getName());
            $class->addStmt(new Node\Stmt\ClassConst([new Node\Const_($constName, new Node\Scalar\String_($column->getName()))], Class_::MODIFIER_PUBLIC));
        }

        // get name method
        $name = $this->factory->method('getName');
        $name->makePublic();
        $name->addStmt(new Node\Stmt\Return_(new Node\Scalar\String_($table->getName())));
        $class->addStmt($name);

        // get columns method
        $items = [];
        foreach ($columns as $column) {
            $columnType = $this->getType($column);

            $constName = 'COLUMN_' . strtoupper($column->getName());
            $items[] = new Node\Expr\ArrayItem(
                new Node\Scalar\LNumber($columnType, ['kind' => Node\Scalar\LNumber::KIND_HEX]),
                new Node\Expr\ClassConstFetch(new Node\Name('self'), $constName)
            );
        }

        $getColumns = $this->factory->method('getColumns');
        $getColumns->makePublic();
        $getColumns->addStmt(new Node\Stmt\Return_(new Node\Expr\Array_($items)));
        $class->addStmt($getColumns);

        // add find methods
        foreach ($columns as $column) {
            $this->buildFindBy($class, $column);
        }

        $this->buildHydrateRow($class, $rowClass, $columns);

        return $class;
    }

    private function buildFindBy(\PhpParser\Builder\Class_ $class, Column $column)
    {
        $methodName = 'findBy' . ucfirst($this->normalizeName($column->getName()));
        $propertyName = ucfirst($this->normalizeName($column->getName()));

        $hydrator = new Node\Expr\Closure();
        $hydrator->params = [
            new Node\Param(new Node\Expr\Variable('row'), null, new Node\Identifier('array'))
        ];
        $hydrator->stmts = [
            new Node\Stmt\Return_(new Node\Expr\MethodCall(new Node\Expr\Variable('this'), new Node\Identifier('newRow'), [
                new Node\Arg(new Node\Expr\Variable('row'))
            ]))
        ];

        $methodCall = new Node\Expr\MethodCall(new Node\Expr\Variable('this'), new Node\Identifier('getAll'), [
            new Node\Arg(new Node\Expr\Variable('startIndex')),
            new Node\Arg(new Node\Expr\Variable('count')),
            new Node\Arg(new Node\Expr\Variable('sortBy')),
            new Node\Arg(new Node\Expr\Variable('sortOrder')),
            new Node\Arg(new Node\Expr\Variable('condition')),
            new Node\Arg(new Node\Expr\ConstFetch(new Node\Name('null'))),
            new Node\Arg($hydrator),
        ]);

        $type = $this->getTypeForColumn($column);

        $findBy = $this->factory->method($methodName);
        $findBy->makePublic();
        $findBy->addParam(new Node\Param(new Node\Expr\Variable('value'), null, $type !== null ? new Node\Identifier($type) : null));
        $findBy->addParam(new Node\Param(new Node\Expr\Variable('startIndex'), new Node\Expr\ConstFetch(new Node\Name('null')), new Node\NullableType(new Node\Identifier('int'))));
        $findBy->addParam(new Node\Param(new Node\Expr\Variable('count'), new Node\Expr\ConstFetch(new Node\Name('null')), new Node\NullableType(new Node\Identifier('int'))));
        $findBy->addParam(new Node\Param(new Node\Expr\Variable('sortBy'), new Node\Expr\ConstFetch(new Node\Name('null')), new Node\NullableType(new Node\Identifier('string'))));
        $findBy->addParam(new Node\Param(new Node\Expr\Variable('sortOrder'), new Node\Expr\ConstFetch(new Node\Name('null')), new Node\NullableType(new Node\Identifier('int'))));
        $findBy->addStmt(new Node\Stmt\Expression(new Node\Expr\Assign(new Node\Expr\Variable('condition'), new Node\Expr\New_(new Node\Name('\\' . Condition::class)))));
        $findBy->addStmt(new Node\Stmt\Expression(new Node\Expr\MethodCall(new Node\Expr\Variable('condition'), new Node\Identifier($this->getOperatorForColumn($column)), [
            new Node\Arg(new Node\Scalar\String_($column->getName())),
            new Node\Arg(new Node\Expr\Variable('value'))
        ])));
        $findBy->addStmt(new Node\Stmt\Return_($methodCall));
        $class->addStmt($findBy);
    }

    private function buildHydrateRow(\PhpParser\Builder\Class_ $class, string $rowClass, array $columns)
    {
        $newRow = $this->factory->method('newRow');
        $newRow->makePublic();
        $newRow->setReturnType(new Node\Name($rowClass));
        $newRow->addParam(new Node\Param(new Node\Expr\Variable('row'), null, new Node\Identifier('array')));
        $newRow->addStmt(new Node\Stmt\Expression(new Node\Expr\Assign(new Node\Expr\Variable('result'), new Node\Expr\New_(new Node\Name($rowClass)))));

        foreach ($columns as $column) {
            $propertyName = ucfirst($this->normalizeName($column->getName()));
            $newRow->addStmt(new Node\Stmt\Expression(new Node\Expr\MethodCall(new Node\Expr\Variable('result'), new Node\Identifier('set' . $propertyName), [
                new Node\Arg(new Node\Expr\ArrayDimFetch(new Node\Expr\Variable('row'), new Node\Scalar\String_($column->getName())))
            ])));
        }

        $newRow->addStmt(new Node\Stmt\Return_(new Node\Expr\Variable('result')));
        $class->addStmt($newRow);
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

    private function getTypeForColumn(Column $column): ?string
    {
        $type = $column->getType();

        if ($type instanceof Types\ArrayType) {
            return null;
        } elseif ($type instanceof Types\BigIntType) {
            return 'int';
        } elseif ($type instanceof Types\BinaryType) {
            return null;
        } elseif ($type instanceof Types\BlobType) {
            return null;
        } elseif ($type instanceof Types\BooleanType) {
            return 'bool';
        } elseif ($type instanceof Types\DateType) {
            return '\\' . Date::class;
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
            return null;
        } elseif ($type instanceof Types\ObjectType) {
            return null;
        } elseif ($type instanceof Types\SimpleArrayType) {
            return 'string';
        } elseif ($type instanceof Types\SmallIntType) {
            return 'int';
        } elseif ($type instanceof Types\StringType) {
            return 'string';
        } elseif ($type instanceof Types\TextType) {
            return 'string';
        } elseif ($type instanceof Types\TimeType) {
            return '\\' . Time::class;
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

    private function getType(Column $column)
    {
        $type = 0;

        if ($column->getLength() > 0) {
            $type+= $column->getLength();
        }

        $type = $type | TypeMapper::getTypeByDoctrineType($column->getType()->getName());

        if (!$column->getNotnull()) {
            $type = $type | TableInterface::IS_NULL;
        }

        if ($column->getAutoincrement()) {
            $type = $type | TableInterface::AUTO_INCREMENT;
        }

        return $type;
    }
}
