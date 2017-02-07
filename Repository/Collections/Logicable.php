<?php namespace Actions\Repository\Collections;


interface Logicable
{
	/**
	 * Обновить данные в логическом элементе
	 * @param $actionId акция
	 * @param $id идентификатор элемента
	 * @param $group_name тип элемента
	 * @param $name имя элемента
	 * @param $value значение элемента
	 *
	 * @return mixed
	 */
	public function updateItem($actionId, $id, $group_name, $name, $value);

	/**
	 * Перерасчет данных
	 * @param $actionId акция
	 * @param $type тип элемента
	 *
	 * @return mixed
	 */
	public function calc($actionId, $type);

	/**
	 * Получение данных из логического элемента
	 * @param $actionId акция
	 * @param $id идентификатор элемента
	 * @param $type тип элемента
	 *
	 * @return mixed
	 */
	public function getItem($actionId, $id, $type);
}