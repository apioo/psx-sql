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
use PSX\Sql\TableAbstract;

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

            $modelClassName = $this->normalizeName($tableName);
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

        $columns = $table->getColumns();
        foreach ($columns as $column) {
            $name = lcfirst($this->normalizeName($column->getName()));
            $type = $this->getTypeForColumn($column);
            
            // property
            $prop = $this->factory->property($name);
            $prop->makeProtected();
            $prop->setDocComment($this->buildComment(['var' => $type !== null ? $type . '|null' : 'mixed']));
            $class->addStmt($prop);

            // setter
            $param = $this->factory->param($name);
            if (!empty($type)) {
                $param->setType(new Node\NullableType($type));
            }

            $setter = $this->factory->method('set' . ucfirst($name));
            $setter->setReturnType('void');
            $setter->makePublic();
            $setter->setDocComment($this->buildComment(['param' => ($type !== null ? $type . '|null' : 'mixed') . ' $' . $name]));
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
            $getter->setDocComment($this->buildComment(['return' => $type !== null ? $type . '|null' : 'mixed']));
            $getter->addStmt(new Node\Stmt\Return_(
                new Node\Expr\PropertyFetch(new Node\Expr\Variable('this'), $name)
            ));
            $class->addStmt($getter);
        }

        return $class;
    }

    private function generateRepository(string $className, string $modelClassName, Table $table)
    {
        $class = $this->factory->class($className);
        $class->extend('\\' . TableAbstract::class);

        $columns = $table->getColumns();

        // constants
        foreach ($columns as $column) {
            $constName = strtoupper($column->getName());
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
            $columnType = 0; // @TODO get column type

            $constName = strtoupper($column->getName());
            $items[] = new Node\Expr\ArrayItem(
                new Node\Scalar\LNumber($columnType, ['kind' => Node\Scalar\LNumber::KIND_HEX]),
                new Node\Expr\ClassConstFetch(new Node\Name('self'), $constName)
            );
        }

        $columns = $this->factory->method('getColumns');
        $columns->makePublic();
        $columns->addStmt(new Node\Stmt\Return_(new Node\Expr\Array_($items)));
        $class->addStmt($columns);

        // add create/update/delete methods

        // add find methods
        // @TODO add find methods for all columns
        
        // add insert/update/delete methods
        // @TODO add insert/update/delete methods

        return $class;
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
}
