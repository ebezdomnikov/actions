<?php namespace Actions\Server;

use Actions\Repository\ElementTypes;

class LogicHandle
{
	/**
	 * @var
	 */
	private $userObjects;
	/**
	 * Загруженные акции клиентов
	 * @var3
	 */
	private $actions;

	public function proc($params)
	{
		$result = [];

		$params     = explode(':', $params);
		$cmd        = $params[1];
		$session_id = $params[2];
		$user_id    = $params[3];

		$uid = md5($session_id . $user_id);

		if ($cmd == 'loadaction')
		{
			$actionId = $params[4];
			$this->loadAction($actionId);
		}
		elseif ($cmd == 'update')
		{
			$actionId    = $params[4];
			$object_type = $params[5];
			$id          = $params[6];
			$name        = $params[7];
			$value       = $params[8];
			$this->updateUserActionObjectValue($uid, $actionId, $object_type, $id, $name, $value);
		}
		elseif ($cmd == 'get')
		{
			$actionId    = $params[4];
			$object_type = $params[5];
			$id          = $params[6];
			$result      = $this->getUserActionObjectItem($uid, $actionId, $object_type, $id);
		}
		elseif ($cmd == 'refresh')
		{
			$actionId    = $params[4];
			$object_type = $params[5];
			$this->refreshUserActionObject($uid, $actionId, $object_type);
		}

		return $result;
	}

	/**
	 * @param $actionId
	 *
	 * @return mixed
	 */
	public function loadAction($actionId)
	{
		if (!isset($this->actions[$actionId]))
		{
			$actionCollection         = app('Actions.CollectionFactory')->forActions();
			$this->actions[$actionId] = $actionCollection->getOne($actionId);
		}

		return $this->actions[$actionId];
	}

	public function updateUserActionObjectValue($uid, $actionId, $object_type, $id, $name, $value)
	{
		$userObject = $this->getUserActionObject($uid, $actionId, $object_type);

		if ($object_type == ElementTypes::ACTION_PRODUCTS
			|| $object_type == ElementTypes::ACTION_CONDITIONS
			|| $object_type == ElementTypes::ACTION_PACKAGE_COUNT
			|| $object_type == ElementTypes::ACTION_STATUS
			|| $object_type == ElementTypes::BONUS_PRODUCTS
		)
		{
			$userObject->update(
				$id,
				$name,
				$value
			);
		}
	}

	public function getUserActionObject($uid, $actionId, $object_type)
	{
		if (!isset($this->userObjects[$uid][$actionId][$object_type]))
		{
			if ($object_type == ElementTypes::ACTION_PRODUCTS)
			{
				// коллекция будет использовать локальное хранилище
				$logicActionProducts = app('Actions.CollectionFactory')->make($object_type . '_local');
				$logicActionProducts->setAction($this->loadAction($actionId));

				// выстраеиваем цепочку модификаторов
				$modifiers = app('Actions.ModificatorsFactory')->forActionProducts();
				$logicActionProducts->setModifier($modifiers);
				$this->userObjects[$uid][$actionId][$object_type] = $logicActionProducts;
			}
			elseif ($object_type == ElementTypes::ACTION_CONDITIONS)
			{
				$collection = app('Actions.CollectionFactory')->make(ElementTypes::ACTION_CONDITIONS . '_local');
				$collection->setAction($this->loadAction($actionId));
				$this->userObjects[$uid][$actionId][$object_type] = $collection;
			}
			elseif ($object_type == ElementTypes::ACTION_PACKAGE_COUNT)
			{
				$collection = app('Actions.CollectionFactory')->make(ElementTypes::ACTION_PACKAGE_COUNT . '_local');
				$collection->setAction($this->loadAction($actionId));
				$this->userObjects[$uid][$actionId][$object_type] = $collection;
			}
			elseif ($object_type == ElementTypes::ACTION_STATUS)
			{
				$collection = app('Actions.CollectionFactory')->make(ElementTypes::ACTION_STATUS . '_local');
				$collection->setAction($this->loadAction($actionId));
				$this->userObjects[$uid][$actionId][$object_type] = $collection;
			}
			elseif ($object_type == ElementTypes::BONUS_PRODUCTS)
			{
				$collection = app('Actions.CollectionFactory')->make($object_type . '_local');
				$collection->setAction($this->loadAction($actionId));
				$this->userObjects[$uid][$actionId][$object_type] = $collection;
			}
		}

		if (isset($this->userObjects[$uid][$actionId][$object_type]))
		{
			;
		}
		{
			return $this->userObjects[$uid][$actionId][$object_type];
		}

		return null;
	}

	public function getUserActionObjectItem($uid, $actionId, $object_type, $id)
	{
		$userObject = $this->getUserActionObject($uid, $actionId, $object_type);

		if (
			$object_type == ElementTypes::ACTION_PRODUCTS
			|| $object_type == ElementTypes::ACTION_CONDITIONS
			|| $object_type == ElementTypes::ACTION_PACKAGE_COUNT
			|| $object_type == ElementTypes::ACTION_STATUS
			|| $object_type == ElementTypes::BONUS_PRODUCTS
		)
		{
			if ($item = $userObject->getById($id))
			{
				return $item->getItem();
			}
		}

		return null;
	}

	public function refreshUserActionObject($uid, $actionId, $object_type)
	{
		$userObject = $this->getUserActionObject($uid, $actionId, $object_type);

		if (
			$object_type == ElementTypes::ACTION_PRODUCTS
			|| $object_type == ElementTypes::ACTION_CONDITIONS
			|| $object_type == ElementTypes::ACTION_PACKAGE_COUNT
			|| $object_type == ElementTypes::ACTION_STATUS
			|| $object_type == ElementTypes::BONUS_PRODUCTS
		)
		{
			$userObject->refresh();
		}
	}
}