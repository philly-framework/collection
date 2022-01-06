<?php
declare(strict_types=1);

namespace Elephox\Collection\Contract;

use IteratorAggregate;

/**
 * @template T
 *
 * @extends GenericCollection<T>
 * @extends IteratorAggregate<never, T>
 */
interface ReadonlySet extends GenericCollection, IteratorAggregate
{
	/**
	 * @param T $value
	 * @return bool
	 */
	public function contains(mixed $value): bool;
}