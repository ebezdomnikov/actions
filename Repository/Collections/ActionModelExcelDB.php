<?php namespace Actions\Repository\Collections;

use DB;

trait ActionModelExcelDB
{
    use ActionItemData;

    use DataSourceSelectable;
    /**
     *
     * @return mixed
     */
    public function getModel()
    {
        if ( empty($this->model) && ! empty($this->actionItem))
        {
            $filename = $this->actionItem->getFilePath();

            $this->model = DB::connection('exceldb')
                ->selectFileName($filename)
                ->open($this->local)
                ->table($this->getRangeName())
                ->withOutFormat()// без формата
                ->withFirstRow()// с первой строкой
            ;
        }

        return $this->model;
    }
}