<?php namespace Actions\Repository\Collections\ActionProducts;


use Actions\Repository\Collections\AbstractCollection;
use Actions\Repository\Collections\ActionModelExcelDB;
use Actions\Repository\Collections\Logicable;
use Actions\Repository\ElementTypes;
use ResolveMap\Map;
use App\Core\Repository\Collections\DrawableCollection;
use App\Core\Repository\Collections\UpdatableCollection;


/**
 * Коллекция товаров
 * @package     Actions\Repository\Collections\ActionProducts
 */
class Collection extends AbstractCollection implements DrawableCollection, UpdatableCollection, Logicable
{
    use ActionModelExcelDB;
    /**
     * Именованная область с товарами
     * @var string
     */
    protected $rangeName = 'товары';


    public function __construct()
    {
        // трансформатор коллекции
        $this->transformer = app('TransformersFactory')->forActionProducts();

        $this->map = new Map();
        $this->map->setAssociative([
            'product_id'         => 'Артикул',
            'name'               => 'Наименование',
            'count'              => 'Заказ штук',
            'volume'             => 'Объем мл',
            'pack'               => 'Шт/уп',
            'price_without_nds'  => 'Цена в рублях без НДС',
            'price'              => 'Цена в рублях c НДС',
            'amount_without_nds' => 'Сумма заказа без НДС',
            'amount'             => 'Сумма заказа с НДС',
            'stock'              => 'Остаток, шт.'
        ]);

        $this->map->setNew(['stock']);

        // порядок отображения столбцов
        $this->map->setOrder([
            'product_id','name','count','stock', 'price','amount'
        ]);
        // переименовать при ресолве ключей
        $this->map->setReName([
            'price' => 'Цена, руб.',
            'amount' => 'Сумма, руб.',
            'count' => 'Заказ, шт.',
            'name' => 'Товар'
        ]);
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
     * Идентификатор коллекции
     * @return string
     */
    public function getName()
    {
        return ElementTypes::ACTION_PRODUCTS;
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

        return view(template('actions.new.products'))
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