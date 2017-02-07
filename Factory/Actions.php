<?php namespace Actions\Factory;

use Actions\Repository\Files\Assets as ActionAssets;
use Actions\Repository\Collections\Actions\Collection as ActionCollection;
use App\Core\Factory\Actions\ActionsFile;
use App\Core\Factory\Actions\ActionWorkTableSKU;
use DB;
use Validator;
use Illuminate\Support\Facades\Input;
use PDO;
use App\Core\Traits\Cacheable;
use App\Core\Traits\RuntimeCacheable;

class Actions
{
	use RuntimeCacheable;
	use Cacheable;

	/**
	 * Текущий клиент на сайте
	 * @var
	 * @since version
	 */
	private $client_id;

	/**
	 * Текущий файл, точнее класс работы с ним
	 * @var ActionsFile
	 */
	private $actionFile;

	/**
	 * Загруженная акция
	 * @var
	 */
	private $action;

	/**
	 * @var ActionCollection
	 */
	private $collection;

	/**
	 * @var ActionAssets
	 */
	private $assets;
	/**
	 * Загруженная информация по акции
	 * @var array
	 */
	private $uplodedActionData = [];
	/**
	 * Actions constructor.
	 */
	public function __construct()
	{
		$this->collection = new ActionCollection();
		// Update файлов делается через Listeners/Events
		$this->assets     = new ActionAssets($this->collection);
		// текущий клиент на сайте
		$this->client_id = (int)app('UsersFactory')->getCurrentClient();
	}

	/**
	 * Получение одной акции
	 * @param $id
	 *
	 * @return mixed
	 */
	public function getOne($id)
	{
		if ( ! $this->can($id)) return false;
		$action = $this->collection->getOne($id);
		return $action;
	}

	/**
	 * Принудительное обновление файлов акции
	 */
	public function updateAsserts()
	{
		$this->assets->update();
	}

	/**
	 * Получить все доступные текущему клиенту акции
	 * @return mixed
	 *
	 * @since version
	 */
	public function getAvailable()
	{
		$actions = $this->collection->all();

		$acceble_actions = [];

		foreach ($actions as $id => $action)
		{
			if ($this->can($id))
				$acceble_actions[$id] = $action;
		}

		return $acceble_actions;
	}

	/**
	 * Загрузка акции чтобы с ней работать
	 * @param $parent_id
	 * @param $id
	 * @param bool $fresh
	 * @return bool
	 */
	public function load($parent_id, $id = null, $fresh = false)
	{
		if (empty($this->action) || $fresh)
			$this->action = $this->collection->getOne($parent_id, $id);
		return true;
	}

	/**
	 * Получение имени акции
	 * @return string
	 */
	public function getName()
	{
		return isset($this->action['name'])?$this->action['name']:"";
	}

	/**
	 * Получение URL описания акции
	 * @return string
	 */
	public function getUrl()
	{
		return isset($this->action['url'])?$this->action['url']:"";
	}

	public function getWorkTable($parent_id, $id)
	{
		$this->saveRuntimeActionData($parent_id, $id);

		if ($actionFile = $this->getActionFile($parent_id, $id))
		{
			return $actionFile->getWorkTable();
		}

		return null;
	}

	/**
	 * Закрываем файл
	 * @param $parent_id
	 * @param $id
	 *
	 *
	 * @since version
	 * @return bool|void
	 */
	public function download($parent_id, $id)
	{
		$this->saveRuntimeActionData($parent_id, $id);

		$action = $this->getOne($parent_id, $id);
		$src = $action['assets']['file']['dst'];

		$this->actionFile = new ActionsFile($parent_id, $id);

		if (
			$this->actionFile->load($src) &&
			$this->actionFile->updateStock() &&
			$this->actionFile->lock()
		)
			return $this->actionFile->download();
		return false;
	}

	/**
	 * Загрузка акции
	 */
	public function upload()
	{
		// получаем данные из запроса
		$file = array('actionFile' => Input::file('actionFile'));
		// пропускаем только Excel файлы
		$rules = array('actionFile' => 'mimetypes:application/vnd.ms-excel,application/msexcel,application/x-msexcel,application/x-ms-excel,application/x-excel,application/x-dos_ms_excel,application/xls,application/x-xls,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'); //mimes:jpeg,bmp,png and for max size max:10000
		// настройка валидации
		$validator = Validator::make($file, $rules);

		$parent_id = (int)request()->input('parent_id');
		$id        = (int)request()->input('id');

		if (empty($parent_id) || empty($id))
		{
			abort(404);
		}

		if ($validator->fails())
		{
			// если ошибка валидации, отправляем на форму акции и пишем ошибки
			return response()->redirectToRoute('getAction',['id' => $parent_id])->withErrors($validator);
		}
		else
		{
			// проверка на валидность файлы
			if (Input::file('actionFile')->isValid())
			{
				$file = Input::file('actionFile');

				$fileName = $file->getClientOriginalName();
				$fileNameExt = $file->getClientOriginalExtension();

				$name = basename($fileName, '.'.$fileNameExt) . '_' . time();

				$outFileName = $name .'.'. $fileNameExt;

				$saveFilePath = $this->getClientStorageUploadActionPath();
				$saveFileName = $outFileName;

				Input::file('actionFile')->move($saveFilePath, $saveFileName);

				\Session::flash('actionUploadedFile', $saveFileName);
				return response()->redirectToRoute('procUploadedAction',['parent_id' => $parent_id, 'id' => $id]);
			}
			else
			{
				return response()->redirectToRoute('getAction',['id' => $parent_id])->with('actionError', 'Загруженный файл поврежден.');
			}
		}
	}

	/**
	 * Отработка загруженного файла
	 * @param $parent_id
	 * @param $id
	 *
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function procUploaded($parent_id, $id)
	{
		$this->saveRuntimeActionData($parent_id, $id);

		$name = \Session::get('actionUploadedFile');

		if (empty($parent_id) || empty($id) || empty($name))
		{
			abort(404);
		}

		$src = $this->getClientStorageUploadActionPath() . DIRECTORY_SEPARATOR . $name;

		$this->actionFile = new ActionsFile($parent_id, $id);

		if ($this->actionFile->load($src))
		{
			$this->uplodedActionData = $this->actionFile->read();

			if ( ! $this->actionFile->isActionCondition())
			{
				return response()->redirectToRoute('getAction',['id' => $parent_id])->with('actionError', 'Условия акции не выполнены. Пожалуйста, проверьте данные.');
			}

			if ($this->addActionsToCart())
			{
				return response()->redirectToRoute('showCart');
			}
			else
			{
				return response()->redirectToRoute('getAction',['id' => $parent_id])->with('actionError', 'Нет данных для загрузки. Возможно вы использовали не тот файл.');
			}
		}
		else
		{
			return response()->redirectToRoute('getAction',['id' => $parent_id])->with('actionError', 'Загруженный файл не соответствует формату.');
		}
	}

	/**
	 * Удаление акции из корзины по сути
	 * @param $parent_id
	 * @param $id
	 *
	 * @return bool|null
	 */
	public function remove($parent_id, $id)
	{
		// Получаем элементы корзины по той же акции, их нужно удалить перед добавлением акции
		$deleteItems = $this->getActionItemsIdAsArray($id);
		if (app('CartFactory')->removeItems($deleteItems))
		{
			return response()->json([
					'status' => 'ok',
					'id' => $id,
					'parent_id' => $parent_id
				]
			);
		}
		else
		{
			return response()->json([
					'status' => 'not ok',
					'id' => 0,
					'parent_id' => 0
				]
			);
		}
	}

	/**
	 * Текущий родитель акции
	 * @return mixed
	 */
	public function getCurParentId()
	{
		return \RuntimeVariable::get('action.parent_id');
	}

	/**
	 * Текущая акция
	 * @return mixed
	 */
	public function getCurId()
	{
		return \RuntimeVariable::get('action.id');
	}

	/**
	 * Обновление кол-ва товара в файле и калькуляция изменений
	 * @param $parent_id
	 * @param $id
	 * @param $product_id
	 * @param $qnty
	 * @param $rowIndex
	 *
	 */
	public function updateQntyInProductAction($parent_id, $id, $product_id, $qnty, $rowIndex)
	{
		$this->saveRuntimeActionData($parent_id, $id);

		if ($actionFile = $this->getActionFile($parent_id, $id))
		{
			$actionFile->updateProductQnty($product_id, $qnty, $rowIndex);
		}
	}

	public function saveWorkTableSku($table_name, $data)
	{
		if ($workTableSKU = $this->getWorkTableSku($table_name))
		{
			$workTableSKU->update($data);
		}
		else
		{
			$key = 'actions.worktable.' . $table_name. '.' . $this->getCurId() . $this->getCurParentId();
			$workTableSKU = new ActionWorkTableSKU($data);
			\RuntimeVariable::set($key,$workTableSKU);
		}
	}

	public function getWorkTableSku($table_name)
	{
		$key = 'actions.worktable.' . $table_name. '.' . $this->getCurId() . $this->getCurParentId();
		return \RuntimeVariable::get($key);
	}

	/**
	 * Получение ссылки на текущий класс работы с файлом
	 * @param $parent_id
	 * @param $id
	 *
	 * @return ActionsFile|null
	 */
	private function getActionFile($parent_id, $id)
	{
		$action = $this->getOne($parent_id, $id);
		$src = $action['assets']['file']['dst'];

		if (empty($this->actionFile))
			$this->actionFile = new ActionsFile($parent_id, $id);

		if ($this->actionFile->load($src))
		{
			return $this->actionFile;
		}
		else
		{
			$this->actionFile = null;
		}

		return null;
	}
	/**
	 * Сохранятем текущие параметры акции в рунтайм
	 * @param $parent_id
	 * @param $id
	 *
	 */
	private function saveRuntimeActionData($parent_id, $id)
	{
		\RuntimeVariable::set('action.parent_id',$parent_id);
		\RuntimeVariable::set('action.id', $id);
	}

	/**
	 * Получение массива Id элемнтов корзины текущей акции
	 * @param null $id
	 * @return array
	 */
	private function getActionItemsIdAsArray($id = null)
	{
		$cart = app('CartFactory');
		$cartItems = $cart->getCart();

		$deleteItems = [];
		if (isset($cartItems['items']))
		{
			foreach ($cartItems['items'] as $cartItem)
			{
				if (isset($cartItem['params']) && isset($cartItem['params']['id_logic']))
					if ($cartItem['params']['id_logic'] == $id)
						$deleteItems[] = $cartItem['item_id'];
			}
		}

		return $deleteItems;
	}

	/**
	 * Добавляем акцию в корзину
	 */
	private function addActionsToCart()
	{
		if (empty($this->uplodedActionData)) return false;

		$cart = app('CartFactory');

		$id_logic = \RuntimeVariable::get('action.id');
		// Получаем элементы корзины по той же акции, их нужно удалить перед добавлением акции
		$deleteItems = $this->getActionItemsIdAsArray(
			$id_logic // текущая акция
		);

		// предварительно нужно удалить все акции из корзины
		app('CartFactory')->removeItems($deleteItems);

		foreach($this->uplodedActionData as $type => $items)
		{
			foreach($items as $item)
			{
				if ($product = app('ProductsFactory')->getProductBySupplierCode($item['sku']))
				{
					$product_id = $product->id;
					$count      = $item['count'];
					$params     = $item['params'];

					// добавляем только для бонусов информацию по складу и поставщику
					if ($type == 'BONUS')
					{
						if ($actionProduct = app('ProductsFactory')->getActionProduct($item['sku'], $id_logic))
						{
							$params['action_wh_id']     = $actionProduct->action_wh_id;
							$params['action_client_id'] = $actionProduct->action_client_id;
						}
					}
					$cart->addProduct($product_id, $count, $params, $type);
				}
			}
		}

		return true;
	}

	/**
	 * Место хранения файлов акций, который загрузил клиент
	 * @return string
	 */
	private function getClientStorageUploadActionPath()
	{
		return storage_path('actions' . DIRECTORY_SEPARATOR . 'upload' . DIRECTORY_SEPARATOR . $this->client_id );
	}

	/**
	 * Может ли акция отработать, проверка по клиенту
	 *
	 * @param $id - идентификатор акции
	 *
	 * @return bool
	 * @since version
	 */
	private function can($id)
	{
		$pdo = DB::getPdo();

		$client_id = $this->client_id;

		$stmt = $pdo->prepare("begin
		    :has_access := PARFUM.MOYSALON.verify_client_action(
											    :client_id,
											    :id
											    );
	        end;");

		$stmt->bindParam(':client_id', $client_id, PDO::PARAM_STR, 11);
		$stmt->bindParam(':id', $id, PDO::PARAM_STR, 11);
		$stmt->bindParam(':has_access', $has_access,  PDO::PARAM_STR, 11);
		$stmt->execute();
		//PARFUM.MOYSALON.verify_client_action(pClient_id number –-код клиента, pId_logic number – код акции)
		return $has_access == 1;
	}



	/**
	 * Получение пути к файлу который будет использвоваться на сайте в дальнейшем
	 * @param $srcRaw - данные из базы
	 *
	 * @return string
	 *
	 * @since version
	 */
	private function makePublicFileActionSrc($srcRaw)
	{
		// 1 проверка является ли файл новее
		// 2 копирование нового, если нужно
		// 3 возврат URL
		return '';
	}


}