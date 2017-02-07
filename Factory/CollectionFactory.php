<?php namespace Actions\Factory;

use Actions\Repository\Collections\ActionProducts\Collection as ActionProductsCollection;
use Actions\Repository\Collections\Conditions\Collection as ActionConditionCollection;
use Actions\Repository\Collections\Bonus\Collection as ActionBonusCollection;
use Actions\Repository\Collections\PackageCount\Collection as ActionPackageCountCollection;

use Actions\Repository\Collections\Actions\Collection as ActionCollection;
use Actions\Repository\Collections\Modifiers\ActionProducts\MapNumericKeys;
use Actions\Repository\Collections\Status\Collection as ActionStatusCollection;
use Actions\Repository\ElementTypes;
use Actions\Repository\Fields\Status;
use App\Core\Repository\AbstractFactory;

class CollectionFactory extends AbstractFactory
{
	public function forActionProducts()
	{
		return $this->getInstance(ElementTypes::ACTION_PRODUCTS);
	}

	public function forActions()
	{
		return $this->getInstance('actions');
	}
	/**
	 * @param $name
	 *
	 * @return mixed
	 */
	public function make($name)
	{

		if ($name == ElementTypes::ACTION_PRODUCTS)
		{
			$collection = new ActionProductsCollection();
			// выстраеиваем цепочку модификаторов
			$modifiers = app('Actions.ModificatorsFactory')->forActionProducts();
			$collection->setModifier($modifiers);
			return $collection;
		}

		if ($name == ElementTypes::ACTION_PRODUCTS . '_local')
		{
			$collection = new ActionProductsCollection();
			$collection->useLocal();
			return $collection;
		}

		if ($name == ElementTypes::ACTION_CONDITIONS)
		{
			$conditions = new ActionConditionCollection();
			$modifier = new MapNumericKeys();
			$conditions->setModifier($modifier);
			return $conditions;
		}

		if ($name == ElementTypes::ACTION_CONDITIONS . '_local')
		{
			$conditions = new ActionConditionCollection();
			$conditions->useLocal();

			$modifier = new MapNumericKeys();
			$conditions->setModifier($modifier);
			return $conditions;
		}

        if ($name == ElementTypes::ACTION_STATUS . '_local')
        {
            $conditions = new ActionStatusCollection();
	        $conditions->useLocal();

	        $modifier = new MapNumericKeys();
	        $conditions->setModifier($modifier);

	        return $conditions;
        }

		if ($name == ElementTypes::ACTION_STATUS)
		{
			$conditions = new ActionStatusCollection();
			$modifier = new MapNumericKeys();
			$conditions->setModifier($modifier);
			return $conditions;
		}

		if ($name == ElementTypes::BONUS_PRODUCTS)
		{
			$collection = new ActionBonusCollection();
			$modifiers = app('Actions.ModificatorsFactory')->getInstance(ElementTypes::BONUS_PRODUCTS);
			$collection->setModifier($modifiers);
			return $collection;
		}

		if ($name == ElementTypes::BONUS_PRODUCTS . '_local')
		{
			$collection = new ActionBonusCollection();
			$modifiers = app('Actions.ModificatorsFactory')->getInstance(ElementTypes::BONUS_PRODUCTS);
			$collection->setModifier($modifiers);
			$collection->useLocal();
			return $collection;
		}

		if ($name == ElementTypes::ACTION_PACKAGE_COUNT)
		{
			$collection = new ActionPackageCountCollection();
			$modifier = new MapNumericKeys();
			$collection->setModifier($modifier);

			return $collection;
		}

        if ($name == ElementTypes::ACTION_PACKAGE_COUNT . '_local')
        {
            $collection = new ActionPackageCountCollection();
            $collection->useLocal();

            $modifier = new MapNumericKeys();
            $collection->setModifier($modifier);

            return $collection;
        }


        if ($name == 'actions')
		{
			return new ActionCollection();
		}
	}
}