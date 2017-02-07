<?php namespace Actions\Repository\Logic;

use Actions\Repository\ElementTypes;

class LogicPackageCount extends AbstractLogic
{
	public function __construct($actionProducts, $userDataCollection, $actionItem)
	{
		// фабрика созданий коллекций
		$collectionFactory = app('Actions.CollectionFactory');
		// тип элемента который обрабытывается текущей логикой
		$type = ElementTypes::ACTION_PACKAGE_COUNT;
		// ключи которые нужно брать из пользовательских данных
		$this->userKeys     = ['count'];
		// ключи которые нужно заменить все без разбора в логике
		$this->forceKeys    = [];
		// ключи которые нужно заменить перед выводом
		$this->replacedKeys = ['amount'];
		// отправляем родителю
		parent::__construct($collectionFactory, $type, $actionProducts, $userDataCollection, $actionItem);
	}
}