<?php namespace Actions\Repository\Collections\Actions;

use App\Core\Models\Action;
use App\Core\Traits\Cacheable;
use App\Core\Transformers\ActionTransformer;
use App\Events\Backend\Collections\Actions\AfterRead as AfterReadActionsCollection;

class Collection
{
	/**
	 * Кеширование
	 */
	use Cacheable;
	/**
	 * @var ActionTransformer
	 */
	private $transformer;

	/**
	 * @var array
	 */
	private $actionsRaw;

	/**
	 * @var Collection
	 */
	private $actions = null;

	/**
	 * Ключ кеша для коллекции
	 * @var string
	 */
	private $cacheKey;

    /**
     * Последний результат выборки
     * @var Collection;
     */
	private $lastResult;


	/**
	 * Collection constructor.
	 */
	public function __construct()
	{
		$this->cacheKey = $this->genCacheKey(__CLASS__);
		// трансформатор коллекции
		$this->transformer = app('TransformersFactory')->forActionCollection();
	}

	/**
	 * Получение обработанных данных по акциям
	 * @return \Illuminate\Support\Collection
	 */
	public function all()
	{
		return $this->get();
	}

    /**
     * Последний результат выборки
     * @return mixed
     */
	public function getLastResult()
    {
        return $this->lastResult;
    }

	/**
	 * Получение информации по одной акции
	 * @param $id
	 *
	 * @return mixed
	 */
	public function getOne($id)
	{
		$actions = $this->get($id);
		return $actions->first();
	}

	/**
	 *
	 */
	private function fireAfterObserver()
	{
		event(new AfterReadActionsCollection($this));
	}

    /**
     * Получение все данных по акция и модификация вывода
     *
     * @param null $id
     * @return \Illuminate\Support\Collection
     */
	private function get($id = null)
	{
		$cacheKey = $this->cacheKey . __FUNCTION__ . (int)$id;

//		if ($cached = $this->getCache($cacheKey))
//		{
//		    $this->lastResult = $cached;
//			$this->fireAfterObserver();
//			return $cached;
//		}

		if (empty($this->actionsRaw))
		{
			if (empty($id))
				$actions = Action::orderBy('ID_HIGHERLOGIC','DESC')->get();
			else
				$actions = Action::where('ID_HIGHERLOGIC',$id)
					->orWhere('ID_LOGIC',$id)
					->orderBy('ID_HIGHERLOGIC','DESC')
					->get();
			if ($actions)
			{
				$this->actionsRaw = collect($actions->toArray());
				// трансформация вывода
				$this->actions    = collect($this->transformer->transformCollection($this->actionsRaw));
			}
		}

        $this->lastResult = $this->actions;

		$this->fireAfterObserver();

		return $this->putCache($cacheKey ,$this->actions);
	}
}