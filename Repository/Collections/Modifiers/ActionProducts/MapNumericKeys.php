<?php namespace Actions\Repository\Collections\Modifiers\ActionProducts;

use Illuminate\Support\Collection;
use Actions\Repository\Collections\Modifiers\CollectionModifier;

/**
 * Переименовать столбцы для карты типа NUM
 * @package     Actions\Repository\Collections\Modifiers\ActionProducts
 */
class MapNumericKeys extends CollectionModifier
{
	/**
	 * @param Collection $collection
	 *
	 * @internal Map $map
	 * @return mixed
	 */
	public function apply(Collection $collection)
	{
		if ($map = $this->getParameter('map'))
		{
			return $this->next($collection->transform(function ($item) use($map)
			{
				return $item->renameNumeric($map);
			}));
		}

		return $this->next($collection);
	}
}