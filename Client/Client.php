<?php namespace Actions\Client;

use Actions\Repository\Collections\Logicable;
use Actions\Repository\Collections\акция;
use Actions\Repository\Collections\тип;
use Actions\Repository\ElementTypes;
use App\Service\RatchetClient\RatchetClient;

/**
 * @package     App
 */
class Client extends RatchetClient implements Logicable
{
	/**
	 * @var
	 */
	private $actionId;

	/**
	 * Client constructor.
	 *
	 * @param $actionId
	 */
	public function __construct($actionId)
	{
		$this->actionId = $actionId;
		parent::__construct('logic');
	}



	/**
	 * @param $actionId
	 *
	 */
	public function loadAction($actionId)
	{
		$this->send('loadaction',[$actionId]);
	}

	/**
	 * @param $actionId
	 * @param $id
	 *
	 * @return array
	 */
	public function getProductItem($actionId, $id)
	{
		return $this->getItem($actionId, $id, ElementTypes::ACTION_PRODUCTS);
	}


	public function getItem($actionId, $id, $type)
	{
		return $this->send('get',[$actionId,$type,$id]);
	}
	/**
	 * @param $actionId
	 * @param $id
	 * @param $name
	 * @param $value
	 */
	public function updateProduct($actionId, $id, $name, $value)
	{
		$this->updateItem($actionId, $id, ElementTypes::ACTION_PRODUCTS, $name, $value);
	}

	public function updateItem($actionId, $id, $group_name, $name, $value)
	{
		$this->send('update',[$actionId,$group_name,$id,$name,$value]);
	}

	/**
	 * @param $actionId
	 *
	 */
	public function productRefresh($actionId)
	{
		$this->refresh($actionId, ElementTypes::ACTION_PRODUCTS);
	}

	public function refresh($actionId, $type)
	{
		$this->send('refresh',[$actionId, $type]);
	}
	/**
	 * @param $colRow
	 *
	 * @return array
	 */
	public function cellRead($colRow)
	{
		return $this->send('readcell',[$this->filename, $colRow]);
	}

	/**
	 * @param       $cmd
	 * @param array $params
	 *
	 * @return array
	 */
	public function send($cmd, $params = [])
	{
		// добавляем к каждому запросу идентификатор пользователя
		array_unshift($params,app('UsersFactory')->Id());
		// добавляем к каждому запросу текущую сессию
		array_unshift($params,\Session::getId());

		return parent::send($cmd, $params);
	}

	/**
	 * @param акция $actionId
	 * @param тип   $type
	 *
	 * @return mixed
	 */
	public function calc($actionId, $type)
	{
		return $this->refresh($actionId, $type);
	}
}