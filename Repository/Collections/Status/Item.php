<?php namespace Actions\Repository\Collections\Status;

use App\Core\Repository\Collections\AbstractItem;
use App\Core\Repository\Collections\DrawableItem;

class Item extends AbstractItem implements DrawableItem
{
	/**
	 *
	 * @return mixed
	 */
	public function draw()
	{
		// номер строки где находится
		$id = $this->getId();

		$value = $this->getParameter('status');

		return view(template('actions.new.status-item'))
			->with(compact('id','value'))
			->render();
	}
}