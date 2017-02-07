<?php namespace Actions\Repository\Collections\Conditions;

use Actions\Repository\Collections\AbstractCollection;
use Actions\Repository\Collections\ActionModelExcelDB;
use Actions\Repository\Collections\Logicable;
use ResolveMap\Map;
use Actions\Repository\ElementTypes;
use App\Core\Repository\Collections\DrawableCollection;



class Collection extends AbstractCollection implements DrawableCollection, Logicable
{
    use ActionModelExcelDB;
	/**
	 * Именованная область с товарами
	 * @var string
	 */
	protected $rangeName = 'условия';

	/**
	 * Collection constructor.
	 */
	public function __construct()
	{
		$this->map = null;

		$this->map = new Map();
		$this->map->setNumeric([
			0 => 'condition',
			1 => 'amount',
			2 => 'inorder',
			3 => 'status'
		]);
	}

	/**
	 * Идентификатор коллекции
	 * @return string
	 */
	public function getName()
	{
		return ElementTypes::ACTION_CONDITIONS;
	}
	/**
	 *
	 * @return mixed
	 */
	public function draw()
	{
		$items = $this->all();
		$actionId = $this->actionItem->getId();

		$type = $this->getName();

		return view(template('actions.new.conditions'))
			->withItems($items)
			->with(compact('actionId','type'))
			->render();
	}

	/**
	 * @param акция         $actionId
	 * @param идентификатор $id
	 * @param тип           $group_name
	 * @param имя           $name
	 * @param значение      $value
	 *
	 * @return mixed
	 */
	public function updateItem($actionId, $id, $group_name, $name, $value)
	{
		return $this->update($id, $name, $value);
	}

	/**
	 * @param акция $actionId
	 * @param тип   $type
	 *
	 * @return mixed
	 */
	public function calc($actionId, $type)
	{
		$this->refresh();
	}

	/**
	 * @param акция         $actionId
	 * @param идентификатор $id
	 * @param тип           $type
	 *
	 * @return mixed
	 */
	public function getItem($actionId, $id, $type)
	{
		return $this->getById($id);
	}
}