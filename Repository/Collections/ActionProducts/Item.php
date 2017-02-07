<?php namespace Actions\Repository\Collections\ActionProducts;

use App\Core\Repository\Collections\AbstractItem;
use App\Core\Repository\Collections\DrawableItem;
use Actions\Repository\Draws\Table\Header;
use Actions\Repository\Draws\Table\ProductRow;
use Actions\Repository\Draws\Table\Separator;

class Item extends AbstractItem implements DrawableItem
{
	/**
	 * @param $name
	 * @param $arguments
	 *
	 * @return int|null
	 */
	protected function call($name, $arguments)
	{
		if ($name == 'getName')
			return $this->getValue('name');
		if ($name == 'getProductId')
			return (int)$this->getValue('product_id');
		return parent::call($name, $arguments);
	}

	/**
	 *
	 * @return mixed
	 */
	public function draw()
	{
		if ($this->isFirst()) // Заголовок
		{
			$header = new Header($this, 'actions.new.products.table_header');
			return $header->render();
		}
		elseif (empty($this->getProductId())) // Разделитель
		{
			$separator = new Separator($this, 'actions.new.products.table_separator');
			return $separator->render();
		}
		elseif ( ! empty($this->getProductId())) // Товар
		{
			$productrow = new ProductRow($this, 'actions.new.products.table_product');
			return $productrow->render();
		}
		else
		{
			return view(template('actions.new.products.table_row'))
				->withItem($this)->render();
		}
	}
}