<?php namespace Actions\Repository\Collections\Modifiers;

use Illuminate\Support\Collection;

abstract class CollectionModifier
{
	/**
	 * Before | After
	 * @var
	 */
	protected $type;
	/**
	 * @var CollectionModifier
	 */
	protected $modifier;
	/**
	 * @var
	 */
	protected $parameters;

	/**
	 * @param $name
	 *
	 * @return mixed
	 */
	public function getParameter($name)
	{
		return $this->parameters[$name];
	}

	/**
	 * @param $name
	 * @param $value
	 *
	 */
	public function setParameter($name, $value)
	{
		$this->parameters[$name] = $value;
	}
	/**
	 * Применить модидификатор к коллекции
	 * @param Collection $collection
	 *
	 * @return mixed
	 */
	public abstract function apply(Collection $collection);

	/**
	 * Следующий в цепочке модификатор
	 * @param CollectionModifier $modifier
	 */
	public function succeedWith(CollectionModifier $modifier)
	{
		$this->modifier = $modifier;
	}

	/**
	 * Запуск следующего модификатора
	 * @param Collection $collection
	 */
	public function next(Collection $collection)
	{
		if ($this->modifier)
		{
			$this->modifier->parameters = $this->parameters;
			return $this->modifier->apply($collection);
		}
		return $collection;
	}
}