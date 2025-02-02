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

namespace PSX\Sql\Tests;

use PHPUnit\Framework\TestCase;
use PSX\Sql\ComparisonOperator;
use PSX\Sql\Condition;

/**
 * ConditionTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://phpsx.org
 */
class ConditionTest extends TestCase
{
    public function testCondition()
    {
        $con = Condition::withAnd()
            ->equals('id', '1');

        $this->assertEquals('WHERE (id = ?)', $con->getStatement());
        $this->assertEquals(['1'], $con->getValues());

        $con = Condition::withAnd()
            ->equals('id', '1');

        $this->assertEquals('WHERE (id = ?)', $con->getStatement());
        $this->assertEquals(['1'], $con->getValues());
    }

    public function testConditionConstructor()
    {
        $con = Condition::withOr()
            ->equals('id', '1')
            ->equals('id', '2');

        $this->assertEquals('WHERE (id = ? OR id = ?)', $con->getStatement());
        $this->assertEquals(['1', '2'], $con->getValues());


        $con = Condition::withOr(
            Condition::withAnd()->equals('id', '1'),
            Condition::withOr()->raw('(1 = 1 OR 2 = 2)')
        );

        $this->assertEquals('WHERE ((id = ?) OR ((1 = 1 OR 2 = 2)))', $con->getStatement());
        $this->assertEquals(['1'], $con->getValues());
    }

    public function testConditionMultiple()
    {
        $con = Condition::withOr()
            ->equals('id', '1')
            ->equals('id', '2');

        $this->assertEquals('WHERE (id = ? OR id = ?)', $con->getStatement());
        $this->assertEquals(['1', '2'], $con->getValues());

        $con2 = Condition::withAnd();
        $con2->equals('id', '1');
        $con2->equals('id', '2');
        $con2->add($con);

        $this->assertEquals('WHERE (id = ? AND id = ? AND (id = ? OR id = ?))', $con2->getStatement());
        $this->assertEquals(['1', '2', '1', '2'], $con2->getValues());
    }

    public function testAdd()
    {
        $con = Condition::withAnd();
        $con->add(new Condition\Basic('id', ComparisonOperator::EQUALS, '1'));
        $con->in('foo', [1, 2]);

        $this->assertEquals('WHERE (id = ? AND foo IN (?,?))', $con->getStatement());
        $this->assertEquals(['1', 1, 2], $con->getValues());
    }

    public function testEquals()
    {
        $con = Condition::withAnd();
        $con->equals('foo', 2);

        $this->assertEquals('WHERE (foo = ?)', $con->getStatement());
        $this->assertEquals([2], $con->getValues());
    }

    public function testNotEquals()
    {
        $con = Condition::withAnd();
        $con->notEquals('foo', 2);

        $this->assertEquals('WHERE (foo != ?)', $con->getStatement());
        $this->assertEquals([2], $con->getValues());
    }

    public function testGreater()
    {
        $con = Condition::withAnd();
        $con->greater('foo', 2);

        $this->assertEquals('WHERE (foo > ?)', $con->getStatement());
        $this->assertEquals([2], $con->getValues());
    }

    public function testGreaterThen()
    {
        $con = Condition::withAnd();
        $con->greaterThan('foo', 2);

        $this->assertEquals('WHERE (foo >= ?)', $con->getStatement());
        $this->assertEquals([2], $con->getValues());
    }

    public function testLower()
    {
        $con = Condition::withAnd();
        $con->less('foo', 2);

        $this->assertEquals('WHERE (foo < ?)', $con->getStatement());
        $this->assertEquals([2], $con->getValues());
    }

    public function testLowerThen()
    {
        $con = Condition::withAnd();
        $con->lessThan('foo', 2);

        $this->assertEquals('WHERE (foo <= ?)', $con->getStatement());
        $this->assertEquals([2], $con->getValues());
    }

    public function testLike()
    {
        $con = Condition::withAnd();
        $con->like('foo', 'bar');

        $this->assertEquals('WHERE (foo LIKE ?)', $con->getStatement());
        $this->assertEquals(['bar'], $con->getValues());
    }

    public function testNotLike()
    {
        $con = Condition::withAnd();
        $con->notLike('foo', 'bar');

        $this->assertEquals('WHERE (foo NOT LIKE ?)', $con->getStatement());
        $this->assertEquals(['bar'], $con->getValues());
    }

    public function testBetween()
    {
        $con = Condition::withAnd();
        $con->between('id', 8, 16);

        $this->assertEquals('WHERE (id BETWEEN ? AND ?)', $con->getStatement());
        $this->assertEquals([8, 16], $con->getValues());
    }

    public function testIn()
    {
        $con = Condition::withAnd();
        $con->in('id', [8, 16]);

        $this->assertEquals('WHERE (id IN (?,?))', $con->getStatement());
        $this->assertEquals([8, 16], $con->getValues());
    }

    public function testNotIn()
    {
        $con = Condition::withAnd();
        $con->notIn('id', [8, 16]);

        $this->assertEquals('WHERE (id NOT IN (?,?))', $con->getStatement());
        $this->assertEquals([8, 16], $con->getValues());
    }

    public function testNil()
    {
        $con = Condition::withAnd();
        $con->nil('foo');

        $this->assertEquals('WHERE (foo IS NULL)', $con->getStatement());
        $this->assertEquals([], $con->getValues());
    }

    public function testNotNil()
    {
        $con = Condition::withAnd();
        $con->notNil('foo');

        $this->assertEquals('WHERE (foo IS NOT NULL)', $con->getStatement());
        $this->assertEquals([], $con->getValues());
    }

    public function testInverse()
    {
        $con = Condition::withAnd();
        $con->notNil('foo');
        $con->setInverse(true);

        $this->assertEquals('WHERE NOT (foo IS NOT NULL)', $con->getStatement());
        $this->assertEquals([], $con->getValues());
    }

    public function testRaw()
    {
        $con = Condition::withAnd();
        $con->raw('foo IN (SELECT id FROM foo WHERE id = ?)', [1]);

        $this->assertEquals('WHERE (foo IN (SELECT id FROM foo WHERE id = ?))', $con->getStatement());
        $this->assertEquals([1], $con->getValues());
    }

    public function testRegexp()
    {
        $con = Condition::withAnd();
        $con->regexp('foo', '[A-z]+');

        $this->assertEquals('WHERE (foo RLIKE ?)', $con->getStatement());
        $this->assertEquals(['[A-z]+'], $con->getValues());
    }

    public function testCount()
    {
        $con = Condition::withAnd();
        $con->equals('id', '1');
        $con->equals('id', '2');
        $con->equals('id', '3');

        $this->assertEquals(3, count($con));
    }

    public function testMerge()
    {
        $con_1 = Condition::withAnd()->equals('id', '1');
        $con_2 = Condition::withAnd()->equals('id', '2');

        $this->assertEquals(true, $con_1->hasCondition());

        $con_1->merge($con_2);

        $this->assertEquals('WHERE (id = ? AND id = ?)', $con_1->getStatement());
        $this->assertEquals(['1', '2'], $con_1->getValues());
        $this->assertEquals(true, $con_1->hasCondition());
    }

    public function testRemoveAll()
    {
        $con = Condition::withAnd()->equals('id', '1');

        $this->assertEquals('WHERE (id = ?)', $con->getStatement());
        $this->assertEquals(['1'], $con->getValues());
        $this->assertEquals(true, $con->hasCondition());

        $con->removeAll();

        $this->assertEquals('WHERE 1 = 1', $con->getStatement());
        $this->assertEquals([], $con->getValues());
        $this->assertEquals(false, $con->hasCondition());
    }

    public function testGetStatement()
    {
        $con = Condition::fromCriteria([
            'foo' => 'bar',
            'bar' => [1, 2],
            'baz' => null,
        ]);

        $this->assertEquals('WHERE (foo = ? AND bar IN (?,?) AND baz IS NULL)', $con->getStatement());
        $this->assertEquals('WHERE (foo = ? AND bar IN (?,?) AND baz IS NULL)', $con->getStatement());
    }

    public function testGetValues()
    {
        $con = Condition::fromCriteria([
            'foo' => 'bar',
            'bar' => [1, 2],
            'baz' => null,
        ]);

        $this->assertEquals(['bar', 1, 2], $con->getValues());
    }

    public function testFromCriteria()
    {
        $con = Condition::fromCriteria([
            'foo' => 'bar',
            'bar' => [1, 2],
            'baz' => null,
        ]);

        $result = $con->toArray();

        $this->assertContainsOnlyInstancesOf(Condition\ExpressionInterface::class, $result);
        $this->assertEquals('WHERE (foo = ? AND bar IN (?,?) AND baz IS NULL)', $con->getStatement());
    }
}
