<?php namespace Actions\Repository\Collections;

use Actions\Repository\Collections\Modifiers\CollectionModifier;
use Illuminate\Support\Collection;
use ResolveMap\Map;
use Traversable;

/**
 * Абстрактная коллекция данных
 * @package     Actions\Repository\Collections
 */
abstract class AbstractCollection implements \IteratorAggregate
{
    /**
     * Преобразователь данных коллекции
     * @var
     */
    protected $transformer;
    /**
     * Именованная область
     * @var string
     */
    protected $rangeName;
    /**
     * Модификтор коллекции
     * @var
     */
    protected $modifier;
    /**
     * Карта для работы с полями данных
     * @var Map
     */
    protected $map;
    /**
     * Коллекция
     * @var
     */
    protected $items;
    /**
     * Модель данных откуда загружается коллекция
     * @var
     */
    protected $model;

    /**
     * Получение элемент коллекции по Id
     *
     * @param $id
     *
     * @return null
     */
    public function getById($id)
    {
        $items = $this->getLastResult();

        if (isset($items[$id]))
        {
            return $items[$id];
        }

        return null;
    }

    /**
     * Последний результат выборки из коллекции
     * @return mixed
     */
    public function getLastResult()
    {
        if (empty($this->items))
        {
            return $this->get();
        }

        return $this->items;
    }

    /**
     * Получение всех товаров акции
     *
     * @param null $id - id акции
     *
     * @param bool $fresh
     *
     * @return Collection
     */
    protected function get($id = null, $fresh = false)
    {
        // Если мы уже считали данные, то возвращаем тот же результат, иначе получил свежую порцию
        // и если внешне что то было сделано оно удалится
        if (empty($this->items) || $fresh)
        {
            $this->_get($id);

            $collection = collect($this->applyModifier());

            $this->setLastResult($collection);
        }
        else
        {
            $collection = $this->getLastResult();
        }

        return $collection;
    }

    /**
     * Получение данных и создание Item в зависимости от типа
     * @param null $id
     *
     * @return $this|Collection|null
     */
    protected function _get($id = null)
    {
        if ($model = $this->getModel())
        {

            $collection = $model->get();

            $collection = collect($collection);
            $c          = 0;
            $collection = $collection->transform(function ( $item ) use ( &$c )
            {
                $c ++;
                $item = app('Actions.ItemFactory')->newItem($this->getName(), $item, $c == 1);

                $item->setMap($this->map);

                return $item;
            });

            $this->setLastResult($collection);

            return $collection;
        }

        return null;
    }

    /**
     * Получение модели
     * @return mixed
     */
    abstract public function getModel();

    /**
     * Идентификатор коллекции
     * @return string
     */
    abstract public function getName();

    /**
     * Поледний результат выборки из коллекции
     *
     * @param $items
     */
    protected function setLastResult($items)
    {
        $this->items = $items;
    }

    /**
     * Применить модификаторы к коллекции
     * @return mixed
     */
    protected function applyModifier()
    {
        if ($this->modifier)
        {
            $this->modifier->setParameter('map', $this->map);
            $this->setLastResult(
                $this->modifier->apply($this->getLastResult())
            );
        }

        return $this->getLastResult();
    }

    /**
     * Задать модификатор коллекции
     * @param CollectionModifier $modifier
     */
    public function setModifier(CollectionModifier $modifier)
    {
        $this->modifier = $modifier;
    }

    /**
     * преобразование результата
     * @return array
     */
    public function toArray()
    {
        $items = $this->all();

        $result = [];

        foreach ($items as $key => $item)
        {
            $result[$key] = $item->getItem();
        }

        return $result;
    }

    /**
     * Получение обработанных данных
     * @return \Illuminate\Support\Collection
     */
    public function all()
    {
        return $this->get();
    }

    /**
     * Обновление данных
     */
    public function refresh()
    {
        $this->get(null,true);
    }

    /**
     * Замена Item
     * @param $id
     * @param $newItem
     *
     */
    public function replaceItem($id, $newItem)
    {
        $this->items[$id] = $newItem;
    }

    /**
     * Итератор
     * @return mixed
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->all());
    }

    /**
     * Получение диапазона данных
     * @return string
     */
    protected function getRangeName()
    {
        return $this->rangeName;
    }
}