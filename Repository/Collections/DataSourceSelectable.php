<?php namespace Actions\Repository\Collections;

trait DataSourceSelectable
{
    protected $local;

    /**
     * Используем локальные данные
     */
    public function useLocal()
    {
        $this->local = true;
    }

    /**
     * Используем удаленные данных
     */
    public function useRemote()
    {
        $this->local = false;
    }

}