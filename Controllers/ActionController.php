<?php namespace Actions\Controllers;


use Actions\Repository\ElementTypes;
use App\Core\Data\Composite\Action as ActionComposite;
use App\Http\Controllers\Controller;
use App\Http\Controllers\LikeApiResponsable;
use Actions\Model\ActionCart;

class ActionController extends Controller
{
	use LikeApiResponsable;
	/**
	 * Create a new controller instance.
	 *
	 */
	public function __construct()
	{
		$this->middleware('auth');
	}

	/**
	 * Обновить корзину акции
	 * @param $id акция
	 * @param $item_id ключ текущих данных, id записи
	 * @param $type
	 * @param $item_type
	 * @param $value
	 *
	 */
	public function update($id, $item_id, $type, $item_type, $value)
	{
		$user_id = app('UsersFactory')->Id();

		// обновляемем или создаем итем в корзине акции
		// в корзине акции есть только позиции Id в таблице акции товаров
		// то есть привязки к товару нет, точнее явной нет...
		$actionCart = ActionCart::where('user_id',$user_id)
			->where('action_id',$id)
			->where('group_name', $type)
			->where('item_name', $item_type)
			->where('item_id', $item_id)
			->first();

		if ($actionCart)
		{
			$actionCart->update(['value'=>$value]);
		}
		else
		{
			$actionCart = new ActionCart();
			$actionCart->action_id = $id;
			$actionCart->user_id = $user_id;
			$actionCart->group_name = $type;
			$actionCart->item_name = $item_type;
			$actionCart->item_id = $item_id;
			$actionCart->value = $value;
			$actionCart->save();
		}

		$composer = new ActionComposite($id);


		$elementsTypes = ElementTypes::enumTypes();

        $response = [];

		if ($element = $composer->getElement($type))
            $response[$type] = $element->toArray();

        foreach ($elementsTypes as $type)
		{
			if ($element = $composer->getElement($type))
				$response[$type] = $element->toArray();
		}


		return $this->responseOk($response,"");
	}
	/**
	 * Отображение страницы акции
	 * @param $id
	 *
	 * @return mixed
	 *
	 */
	public function index($id)
	{
		if ($action = app('Actions')->getOne($id))
		{
			app('SEOFactory')->setTitle('Акция - ' . $action->getName());

			$compose = new ActionComposite($id);
			$content = $compose->compose();

			return view(template('actions.index'))
				->withAction($action)
				->withContent($content);
		}
		else
			abort(404);
	}

	/**
	 * Скачивание бланка пакета акции
	 * @param $parent_id - родительская акция
	 * @param $id - пакет
	 *
	 * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
	 *
	 * @since version
	 */
	public function get($parent_id, $id)
	{
		if ( ! app('Actions')->download($parent_id, $id) )
			return response()->redirectTo('/action/'.$parent_id)->with('actionError','Ошибка при загрузке акции. Попробуйте позже');
		return false;
	}

	/**
	 * Загрузка файла акции
	 */
	public function upload()
	{
		return app('Actions')->upload();
	}

	/**
	 * @param $parent_id
	 * @param $id
	 *
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function proc($parent_id, $id)
	{
		return app('Actions')->procUploaded($parent_id, $id);
	}

	/**
	 * Изменение кол-ва товара в форме акции
	 *
	 * @param $parent_id
	 * @param $id
	 * @param $product_id
	 * @param $qnty
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function qnty($parent_id, $id, $product_id, $qnty, $rowIndex)
	{
		return $this->responseOk(
			app('Actions')->updateQntyInProductAction($parent_id, $id, $product_id, $qnty, $rowIndex)
			,'privet');
	}
}


/**
 * 	Route::get('action3',function(){
Excel::selectSheetsByIndex(0)->load('/tmp/file.xlsx', function($reader) {

$reader->noHeading();

$actionId = 0;
$packegesCount = 0;
$actionStatus = false;
$skuCell = [];
$countCell = [];
$cRow = 1;

$reader->each(function($row) use(&$countCell, &$skuCell, &$cRow, &$actionId, &$packegesCount, &$actionStatus)
{
for($col = 1; $col<=$row->count(); $col++)
{
$cellContent = trim($row->get($col));//dump($cellContent);
if ($cellContent)
{
if (mb_ereg_match("SAP номер акции",$cellContent))
{
$actionId = (int)$row->get($col+1);
}
elseif (mb_ereg_match('Введите кол-во пакетов',$cellContent))
{
$packegesCount = (int)$row->get($col+1);
}
elseif(mb_ereg_match('Статус акции', $cellContent))
{
$actionStatus = ($row->get($col+1) == 'OK');
}
elseif(mb_ereg_match('Артикул', $cellContent))
{
$skuCell = [$cRow,$col];
}
elseif(mb_ereg_match('Заказ штук', $cellContent))
{
$countCell = [$cRow,$col];
}
}
}
$cRow++;
});

$reader->skip($skuCell[0])->get([$skuCell[1],$countCell[1]])->each(function($r) use($countCell, $skuCell)
{
$sku = (int)$r->get($skuCell[1]);
$count = $r->get($countCell[1]);
if ($count > 0)
{
if ($product = app('ProductsFactory')->getProductBySupplierCode($sku))
{
dump($product->name);
}
else
{
dump('Нет товара'.$sku);
}
}
});

dump('SAP:'.$actionId);
dump('Packs count:' . $packegesCount);
dump('actionStatus: '. $actionStatus);
dump('skuCell: ', $skuCell);
dump('countCell: ', $countCell);

//dd($results->getItems());
//			foreach ($reader as $row)
//			{
//				$results = $reader->get();
//			}

})->get();


});
 *
 *
 * 		collect($array)->transform(function($item, $col) {
foreach ($item as $letter => $value)
{
if ($value = $this->cell->getUserCellValue($letter.$col))
{
$this->cell->setCellValue($letter.$col, $value);
}
}
});

\PHPExcel_Calculation::getInstance($this->worksheet->getParent())->clearCalculationCache();
 */