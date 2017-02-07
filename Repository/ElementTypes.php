<?php namespace Actions\Repository;

class ElementTypes
{
	const ACTION_PRODUCTS = 'actionproducts';

	const BONUS_PRODUCTS = 'actionbonus';

	const ACTION_CONDITIONS = 'actionconditions';

	const ACTION_PACKAGE_COUNT = 'actionpackagecount';

	const ACTION_STATUS = 'actionstatus';

	static function enumTypes()
	{
		$oClass = new \ReflectionClass(__CLASS__);
		return $oClass->getConstants();
	}
}