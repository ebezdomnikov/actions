<?php namespace Actions\Repository\Draws\Table;

class ProductRow
{
	private $item;

	private $view;

	private $columnView = [
		'count'   => 'actions.new.products.table_product_count',
		'stock'   => 'actions.new.products.table_product_stock',
	    'default' => 'actions.new.products.table_product_default'
	];

	public function __construct($item, $view)
	{
		$this->item = $item;

		$this->view = $view;
	}

	public function render()
	{
		$content = "";

		$inStock = ($stock = $this->item->getParameter('stock')) > 0;

		foreach ($this->item->getFilteredRow() as $key => $value)
		{
			$item_type = $key;

			if (isset($this->columnView[$key]))
			{
				$content .= view(template($this->columnView[$key]))
					->withValue($value)
					->withId($this->item->getId())
					->with(compact('item_type','inStock'));
			}
			else
			{
				$content .= view(template($this->columnView['default']))
					->withValue($value)
					->withId($this->item->getId())
					->with(compact('item_type','inStock'))
					->render();
			}
		}

		return view(template($this->view))
			->withContent($content)
			->render();
	}
}