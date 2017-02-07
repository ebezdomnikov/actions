<?php namespace Actions\Repository\Collections;

use Actions\Repository\Collections\Actions\Item as ActionItem;

trait ActionItemData
{
    /**
     * @var ActionItem
     */
    private $actionItem;

    /**
     * @param ActionItem $actionItem
     *
     */
    public function setAction(ActionItem $actionItem)
    {
        $this->actionItem = $actionItem;
    }

}