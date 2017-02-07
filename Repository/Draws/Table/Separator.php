<?php namespace Actions\Repository\Draws\Table;

class Separator
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
		$separator = "";
		$colCount = 0;
		foreach ($this->item->getFilteredRow() as $value)
		{
			$separator .= $value;
			$colCount++;
		}

		$separator = trim($separator);
		$separator = preg_replace("/\s+/", " ",$separator);


		return view(template($this->view))
			->with(compact('separator', 'colCount'))
			->render();
	}
}