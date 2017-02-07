<?php namespace Actions\Factory;

use Actions\Repository\ElementTypes;
use App\Core\Repository\AbstractFactory;
use App\Core\Repository\ActionTransformer;

class RemoteLogic extends AbstractFactory
{
	/**
	 * @param $name
	 *
	 * @return mixed
	 */
	public function make($name)
	{
		if ($name == ElementTypes::ACTION_PRODUCTS)
		{

		}
		elseif ($name == ElementTypes::ACTION_CONDITIONS)
		{

		}
		elseif ($name == ElementTypes::ACTION_PACKAGE_COUNT)
		{

		}

	}
}