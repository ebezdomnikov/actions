<?php namespace Actions\Repository\Draws;

class Conditions
{
	private $item;

	private $view;

	public function __construct($item, $view)
	{
		$this->item = $item;

		$this->view = $view;
	}

	public function render()
	{
		$captions = [];

		foreach ($this->item->getRow() as $key => $item)
		{
			if ($map = $this->item->getMap())
			{
				$captions[$key] = $map->resolve($key);
			}
		}

		return view(template($this->view))
			->withCaptions($captions)
			->render();
	}
}