<?php namespace Actions\Repository\Logic;

use Actions\Client\Client;
use Actions\Repository\ElementTypes;
use App\Core\Repository\AbstractFactory;

abstract class AbstractLogic
{
	/**
	 * Коллекция к которой будет применена логика
	 * @var
	 */
	protected $procCollection;
	/**
	 * Данные клиента
	 * @var
	 */
	protected $userDataCollection;
	/**
	 * Текущая акция
	 * @var
	 */
	protected $actionItem;
	/**
	 * Элемент логики (локальный)
	 * @var
	 */
	protected $localLogic;

	/**
	 * Удаленная логика
	 * @var Client
	 */
	protected $remoteLogic;

	/**
	 * @var
	 */
	protected $currentLogic;

	/**
	 * @var ElementTypes
	 */
	protected $type;

	/**
	 * @var
	 */
	protected $useLocalLogic = false;

	/**
	 * @var
	 */
	protected $useRemoteLogic = false;

	/**
	 * @var AbstractFactory
	 */
	protected $collectionFactory;
	/**
	 * @var
	 */
	protected $userKeys;
	/**
	 * @var
	 */
	protected $forceKeys;

	/**
	 * @var
	 */
	protected $replacedKeys;

	/**
	 * LogicProducts constructor.
	 *
	 * @param $procCollection
	 * @param $userDataCollection
	 * @param $actionItem
	 */
	public function __construct( AbstractFactory $collectionFactory,  $type,  $procCollection, $userDataCollection, $actionItem )
	{
		$this->collectionFactory = $collectionFactory;
		$this->type = $type;
		$this->procCollection = $procCollection;
		$this->userDataCollection = $userDataCollection;
		$this->actionItem = $actionItem;

		$this->init();
	}

	protected function isRemoteLogic()
	{
		return $this->useRemoteLogic;
	}

	protected function isLocalLogic()
	{
		return $this->useLocalLogic;
	}

	/**
	 * Иницииализация нужных объектов
	 */
	protected function init()
	{
		// удаленная логика
		$this->remoteLogic = new Client($this->actionItem->getId());
		if ($this->remoteLogic->isAlive())
		{
			$this->useRemoteLogic = true;
			$this->remoteLogic->loadAction($this->actionItem->getId());
			$this->currentLogic = $this->remoteLogic;
		}
		else
		{
			// для использования локальной логики
			$this->useRemoteLogic = true;
			$logicActionProducts = $this->collectionFactory->make($this->type);
			$logicActionProducts->setAction($this->actionItem);
			$logicActionProducts->refresh(); //получить новые данные

			$this->localLogic = $logicActionProducts;
			$this->currentLogic = $this->localLogic;
		}
	}

	/**
	 * Обновления значения для логики
	 * @param $id
	 * @param $name
	 * @param $value
	 *
	 */
	protected function updateItem($id, $name, $value)
	{
		$this->currentLogic->updateItem(
			$this->actionItem->getId(), // номер акции
			$id, // идентификатор элемента
			$this->type,  // тип элемента
			$name, // имя элемента
			$value // значение элемента
		);
	}

	/**
	 * запуск логики
	 * здесь по сути запрос данных из экселя
	 */
	public function calc()
	{
		$actionId = $this->actionItem->getId();
		$this->currentLogic->calc($actionId, $this->type);
	}

	/**
	 * Получение значения из логики
	 * @param $id
	 *
	 * @return mixed
	 */
	private function getItem($id)
	{
		$actionId = $this->actionItem->getId();
		// возвращет ассоциативный массив
		return $this->currentLogic->getItem($actionId, $id, $this->type);
	}

	/**
	 * Замена данными из логики данных для отображения
	 *
	 * @param $fields
	 *
	 * @return bool
	 */
	protected function replace()
	{
		$fields = $this->replacedKeys;

		if (empty($fields) || ! is_array($fields))
			return false;

		$items = $this->procCollection->all();

		foreach ($items as $id => $item)
		{
			if ($procItem = $this->getItem($id))
			{
				foreach ($fields as $field)
				{
					if (key_exists($field,$procItem))
						$item->setParameter($field, $procItem[$field]);
				}
			}
		}

		return true;
	}

	/**
	 * применить логику
	 * @param $userKeys
	 * @param $forcedKeys
	 *
	 * @return bool
	 */
	public function apply()
	{
		$userKeys = $this->userKeys;
		$forcedKeys = $this->forceKeys;

		if (! is_array($userKeys) || ! is_array($forcedKeys))
			return false;

		$items = $this->procCollection->all();
		// заносим пользовательские данные, если они есть
		// у нас это кол-во товара, но в принципе можно все что угодно совмещать

		foreach ($items as $id => $item)
		{
			$item_id = $item->getId();

			foreach ($userKeys as $name)
			{
				if ($userValue = $this->getUserValue(
					$item_id,
					$name,
					$this->type
				)
				)
				{
					// обновляем данные в объете для логики
					$this->updateItem($item_id, $name, $userValue);
					// обновляем данные в коллекции которая пойдет на отображение
					$item->setParameter($name, $userValue);
				}
			}
			// обязательные ключи, пока это только цена товара
			foreach ($forcedKeys as $name)
			{
				$this->updateItem($item_id, $name, $item->getParameter($name));
			}

		}

		// далее нужно прогнать через эксель данные
		// для этого загружаем в эксель данные
		// для нас это будут цены и кол-во товара

		// запуск расчета
		$this->calc();
		// замена данных
		$this->replace();
		// результат должные быть procCollection, так эта коллеция потом подет на отображение
		return $this->procCollection;
	}

	/**
	 * Получаем из коллекции данных пользователя необходимые данные
	 * @param $id
	 * @param $name
	 *
	 * @return null
	 */
	private function getUserValue($id, $name, $group_name)
	{
		$finded = $this->userDataCollection->search(function($item) use($id, $name, $group_name)
		{
			return $item['item_name'] == $name
			&& $item['item_id'] == $id
			&& $item['group_name'] == $group_name;
		});

		if ($finded !== false)
		{
			return $this->userDataCollection[$finded]['value'];
		}

		return null;
	}


}