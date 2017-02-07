<?php namespace Actions\Factory;

use Actions\Repository\Collections\ActionProducts\Item as ActionProductsItem;
use Actions\Repository\Collections\Bonus\Item as BonusItem;
use Actions\Repository\Collections\Conditions\Item as ConditionItem;
use Actions\Repository\Collections\PackageCount\Item as PackageCountItem;
use Actions\Repository\Collections\Status\Item as StatusItem;
use Actions\Repository\ElementTypes;

/**
 * Фабрика создания Item для коллекций
 * @package     Actions\Factory
 */
class ItemFactory
{

    /**
     * Создание нового Item в зависимости от типа
     * @param $type
     * @param $item
     * @param bool $first
     *
     * @return ActionProductsItem|BonusItem|ConditionItem|PackageCountItem
     * @throws \Exception
     */
	public function newItem($type, $item, $first = false)
	{
		if ($type == ElementTypes::ACTION_PRODUCTS) //для товаров акции
		{
		    return new ActionProductsItem($item, $first);
		}
        elseif ($type == ElementTypes::ACTION_PACKAGE_COUNT) // кол-во пакетов
        {
            return new PackageCountItem($item, $first);
        }
        elseif ($type == ElementTypes::ACTION_CONDITIONS) // условия акции
        {
            return new ConditionItem($item, $first);
        }
        elseif ($type == ElementTypes::BONUS_PRODUCTS) // бонусный товар
        {
            return new BonusItem($item, $first);
        }
        elseif ($type == ElementTypes::ACTION_STATUS)
        {
	        return new StatusItem($item, $first);
        }

        throw new \Exception("Unknown type");
	}
}