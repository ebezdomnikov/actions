<?php namespace Actions\Repository\Collections\Modifiers\ActionProducts;

use Illuminate\Support\Collection;
use Actions\Repository\Collections\Modifiers\CollectionModifier;

/**
 * Применить карту и переименовать столбцы
 * @package     Actions\Repository\Collections\Modifiers\ActionProducts
 */
class MapKeys extends CollectionModifier
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
				return $item->renameKeys($map);
			}));
		}

		return $this->next($collection);
	}

}