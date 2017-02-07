<?php namespace Actions\Factory;

use Actions\Repository\Collections\Modifiers\ActionProducts\AddColumns;
use Actions\Repository\Collections\Modifiers\ActionProducts\AddProductInfo;
use Actions\Repository\Collections\Modifiers\ActionProducts\ChangeOrder;
use Actions\Repository\Collections\Modifiers\ActionProducts\MapKeys;
use Actions\Repository\Collections\Modifiers\ActionProducts\MapNumericKeys;
use Actions\Repository\ElementTypes;
use App\Core\Repository\AbstractFactory;

class ModificatorsFactory extends AbstractFactory
{
	public function forActionPackageCount()
	{
		return $this->getInstance(ElementTypes::ACTION_PACKAGE_COUNT);
	}


	public function forActionProducts()
	{
		return $this->getInstance(ElementTypes::ACTION_PRODUCTS);
	}

	public function forActionBonus()
	{
		return $this->getInstance(ElementTypes::BONUS_PRODUCTS);
	}
	/**
	 * @param $name
	 *
	 * @return mixed
	 */
	public function make($name)
	{
		if ($name == ElementTypes::ACTION_PRODUCTS || ElementTypes::BONUS_PRODUCTS)
		{
			$mapActionTableKeys     = new MapKeys();
			$addColumns             = new AddColumns();
			$reOrderColumns         = new ChangeOrder();
			$addProductInfoModifier = new AddProductInfo();
			$mapActionTableKeys->succeedWith($addColumns);
			$addColumns->succeedWith($reOrderColumns);
			$reOrderColumns->succeedWith($addProductInfoModifier);

			return $mapActionTableKeys;
		}

		if ($name == ElementTypes::ACTION_PACKAGE_COUNT)
		{
			$mapActionTableKeys     = new MapNumericKeys();
			return $mapActionTableKeys;
		}
	}
}