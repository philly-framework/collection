<?php

namespace Elephox\Collection;

use Elephox\Collection\Contract\Comparable;
use InvalidArgumentException;
use JetBrains\PhpStorm\ExpectedValues;
use JetBrains\PhpStorm\Pure;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @covers \Elephox\Collection\DefaultEqualityComparer
 */
class DefaultEqualityComparerTest extends TestCase
{
	public function equalsDataProvider(): iterable
	{
		yield [1, 1, true];
		yield [0, 0, true];
		yield [1, 0, false];
		yield [0, 1, false];
		yield [1, 2, false];
		yield [1.0, 1.0, true];
		yield [1.0, 1.1, false];
		yield [true, true, true];
		yield [true, false, false];
		yield [false, false, true];
		yield ['a', 'a', true];
		yield ['a', 'b', false];
		yield ['a', 'A', false];
		yield ['1', 1, true];
		yield [true, 1, true];
		yield [false, 1, false];
		yield [0, false, true];
		yield ['0', false, true];
		yield [[], false, true];
		yield [null, false, true];
		yield [null, null, true];
		yield [null, [], true];

		$test1 = new TestComparable();
		$test2 = new TestComparable();
		$test2->value = 2;

		yield [$test1, $test1, true];
		yield [$test1, $test2, false];
		yield [1, $test1, false];
		yield [$test1, new stdClass(), false];
		yield [new stdClass(), $test1, false];
	}

	/**
	 * @dataProvider equalsDataProvider
	 */
	public function testEquals(mixed $a, mixed $b, bool $result): void
	{
		self::assertSame($result, DefaultEqualityComparer::equals($a, $b));
	}

	public function sameDataProvider(): iterable
	{
		yield [1, 1, true];
		yield [1, 2, false];
		yield [null, false, false];
		yield [null, null, true];
		yield [null, [], false];
		yield [1.1, 1, false];
		yield ['1', 1, false];

		$test1 = new TestComparable();
		$test2 = new TestComparable();
		$test2->value = 2;

		yield [$test1, $test1, true];
		yield [$test1, $test2, false];
		yield [1, $test1, false];
		yield [$test1, new stdClass(), false];
		yield [new stdClass(), $test1, false];
	}

	/**
	 * @dataProvider sameDataProvider
	 */
	public function testSame(mixed $a, mixed $b, bool $result): void
	{
		self::assertSame($result, DefaultEqualityComparer::same($a, $b));
		self::assertSame(!$result, DefaultEqualityComparer::invert(DefaultEqualityComparer::same(...))($a, $b));
	}

	public function compareDataProvider(): iterable
	{
		yield [1, 1, 0];
		yield [1, 2, -1];
		yield [2, 1, 1];
		yield [1.1, 1, 1];
		yield [1.1, 1.1, 0];
		yield [1.1, 1.2, -1];
		yield [new stdClass(), new stdClass(), 0];

		$test1 = new TestComparable();
		$test2 = new TestComparable();
		$test2->value = 2;

		yield [$test1, $test1, 0];
		yield [$test1, $test2, -1];
		yield [$test2, $test1, 1];
		yield [$test1, new stdClass(), 1];
		yield [new stdClass(), $test1, 1];
	}

	/**
	 * @dataProvider compareDataProvider
	 */
	public function testCompareAndInvert(mixed $a, mixed $b, int $result): void
	{
		self::assertSame($result, DefaultEqualityComparer::compare($a, $b));
		self::assertSame(-$result, DefaultEqualityComparer::invert(DefaultEqualityComparer::compare(...))($a, $b));
	}

	public function invalidCompareDataProvider(): iterable
	{
		$test1 = new TestComparable();

		yield [1, $test1];
		yield [new stdClass(), true];
	}

	/**
	 * @dataProvider invalidCompareDataProvider
	 */
	public function testInvalidCompare(mixed $a, mixed $b): void
	{
		$this->expectException(InvalidArgumentException::class);
		DefaultEqualityComparer::compare($a, $b);
	}

	public function testInvalidInvertCallable(): void
	{
		$cb = fn() => "text";

		$inverted = DefaultEqualityComparer::invert($cb);

		$this->expectException(InvalidArgumentException::class);
		$inverted(1, 2);
	}
}

class TestComparable implements Comparable
{
	public int $value = 1;

	#[Pure]
	#[ExpectedValues([-1, 0, 1])]
	public function compareTo(object $other): int
	{
		if (!($other instanceof self))
		{
			return 1;
		}

		return $this->value <=> $other->value;
	}
}