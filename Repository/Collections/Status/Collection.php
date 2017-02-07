<?php namespace Actions\Repository\Collections\Status;

use Actions\Repository\Collections\AbstractCollection;
use Actions\Repository\Collections\ActionModelExcelDB;
use Actions\Repository\Collections\Logicable;
use Actions\Repository\ElementTypes;
use ResolveMap\Map;
use App\Core\Repository\Collections\DrawableCollection;


class Collection extends AbstractCollection implements DrawableCollection, Logicable
{
    use ActionModelExcelDB;
	/**
	 * Именованная область с товарами
	 * @var string
	 */
	protected $rangeName = 'статус';

    /**
     * Collection constructor.
     */
    public function __construct()
	{
		$this->map = new Map();

		$this->map->setNumeric([
			0 => 'status',
		]);

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

		return view(template('actions.new.status'))
			->withItems($items)
			->with(compact('actionId','type'))
			->render();
	}

	/**
	 * Идентификатор коллекции
	 * @return string
	 */
	public function getName()
	{
		return ElementTypes::ACTION_STATUS;
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
     * @param $id
     * @param $type
     * @param $value
     *
     */
    public function update($id, $type, $value)
    {
        $updatedValues = [];

        $resolvedName = $this->map->resolve($type, false);
        $updatedValues[$resolvedName] = $value;

        $model = $this->getModel();

        $model->where('id', $id)
            ->update($updatedValues);
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