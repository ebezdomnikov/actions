<?php namespace Actions\Repository\Collections\Bonus;

use Actions\Repository\Collections\AbstractCollection;
use Actions\Repository\Collections\ActionModelExcelDB;
use Actions\Repository\ElementTypes;
use ResolveMap\Map;

use App\Core\Repository\Collections\DrawableCollection;

class Collection extends AbstractCollection  implements DrawableCollection
{
    use ActionModelExcelDB;

	/**
	 * Именованная область с товарами
	 * @var string
	 */
	protected $rangeName = 'бонус';

    /**
     * Collection constructor.
     */
	public function __construct()
	{
		$this->map = new Map();

		$this->map->setAssociative([
			'product_id'         => 'Артикул',
			'name'               => 'Наименование',
			'countro'            => 'Заказ штук',
			'stock'              => 'Остаток, шт.'
		]);

		$this->map->setNew(['stock']);

		// порядок отображения столбцов
		$this->map->setOrder([
			'product_id','name','countro','stock'
		]);
		// переименовать при ресолве ключей
		$this->map->setReName([
			'countro' => 'Заказ, шт.',
			'name' => 'Товар'
		]);
	}

	/**
	 * Идентификатор коллекции
	 * @return string
	 */
	public function getName()
	{
		return ElementTypes::BONUS_PRODUCTS;
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

		return view(template('actions.new.bonus'))
			->withItems($items)
			->with(compact('actionId','type'))
			->render();
	}
}