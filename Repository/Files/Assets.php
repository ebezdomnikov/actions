<?php namespace Actions\Repository\Files;

use Actions\Repository\Collections\Actions\Collection;

/**
 * @package     Actions\Repository\Files
 */
class Assets
{
    /**
     * @var Collection
     */
    private $actionsCollection;

    /**
     * Assets constructor.
     * @param Collection $actionsCollection
     */
    public function __construct(Collection $actionsCollection)
    {
        $this->actionsCollection = $actionsCollection;
    }
    /**
     * Обновить файлы акции, если нужно
     * Проверка будет по дате редактирования
     * @since version
     */
    public function update()
    {
        foreach ($this->actionsCollection->getLastResult() as $action)
        {
            foreach ($action->getAssetsPaths() as $assetsPath)
            {
                $this->copyResource($assetsPath->src, $assetsPath->dst);
            }
        }
    }

    /**
     * Копирование ресурсов,
     *
     * @param $src     - источник
     * @param $dst     - назначение
     * @param $onlyNew - только новые файлы
     *
     * @since version
     * @return bool
     */
    private function copyResource($src, $dst, $onlyNew = true)
    {
        // Если файлы источника нет, то ничего не делаем.
        if ( ! file_exists($src))
            return false;

        $dirname = dirname($dst);

        // Создаем директории, если их нет
        if ( ! file_exists($dirname) && ! mkdir($dirname,0777,true))
            return false;

        if (
            $onlyNew && // Только если обновление
            $this->isResourceNew($src, $dst) && // Только новый файл и если он действительно новый
            $this->copyFile($src, $dst) // копировать файл
        )
        {
            // задаем время модификации файла равному источнику, чтобы отрабатывал корректно isResourceNew
            return $this->setFileMDate($src, $dst);
        }
        elseif(
            ! $onlyNew && // если Force копирование
            $this->copyFile($src, $dst) // копировать файл
        )
        {
            // задаем время модификации файла равному источнику, чтобы отрабатывал корректно isResourceNew
            return $this->setFileMDate($src, $dst);
        }

        return false;
    }

    /**
     * Копирование файла
     * @param $src
     * @param $dst
     *
     * @return bool
     *
     * @since version
     */
    private function copyFile($src, $dst)
    {
        return copy($src, $dst);
    }

    /**
     * Задание даты модификации
     * @param $src
     * @param $dst
     *
     * @return bool
     *
     * @since version
     */
    private function setFileMDate($src, $dst)
    {
        return touch($dst, filemtime($src));
    }
    /**
     * Проверка является ли запрашиваемый файл новее имеющегося
     *
     * @param $src - источник
     * @param $dst - назначение
     *
     * @since version
     * @return bool
     */
    private function isResourceNew($src, $dst)
    {
        if ( ! file_exists($dst)) // нет файла еще, просто возвращаеме true чтобы система скопировала новый файл
            return true;
        return filemtime($src) > filemtime($dst);
    }
}
