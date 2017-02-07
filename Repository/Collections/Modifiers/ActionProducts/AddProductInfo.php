<?php namespace Actions\Repository\Collections\Modifiers\ActionProducts;

use App\Core\Repository\Collections\Filters\Products\ClientFilter;
use App\Core\Repository\Collections\Filters\Products\ProductSkuFilter;
use App\Core\Repository\Collections\Products\Collection as ProductsCollection;

use Illuminate\Support\Collection;
use Actions\Repository\Collections\Modifiers\CollectionModifier;


/**
 * Добавление в коллекцию информации о товарах
 * @package     Actions\Repository\Collections\Modifiers\ActionProducts
 */
class AddProductInfo extends CollectionModifier
{
	/**
	 * @var Collection
	 */
	private $products;
	/**
	 * @var
	 */
	private $productsCollection;
    /**
     * @var int
     */
	private $price_id;
    /**
     * @var int
     */
	private $client_id;
    /**
     * @var int
     */
	private $pos_id;

    /**
     * AddProductInfo constructor.
     */
	public function __construct()
    {
        $this->price_id  = (int)get_user_price();
        $this->pos_id    = (int)app('UsersFactory')->getCurrentPos();
        $this->client_id = (int)app('UsersFactory')->getCurrentClient();
    }

    /**
	 * @param Collection $collection
	 *
	 * @return Collection
	 */
	public function apply(Collection $collection)
	{
		$supplierIds = collect($collection->all())->transform(function($item){
			return (int)$item->getProductId();
		})->reject(function($item){
			return empty($item);
		});

		$productCollection = new ProductsCollection();
        $productCollection->applyFilter(new ProductSkuFilter($supplierIds->toArray()));
        $productCollection->applyFilter(new ClientFilter($this->client_id, $this->price_id, $this->pos_id));

		$this->products = $productCollection;
		$this->productsCollection = collect($productCollection->get());

		$collection = $collection->transform(function($item)
		{
			if ( ! empty((int)$item->getProductId()))
			{
				if ($id = $this->getByProductId($item->getProductId()))
				{
					$params = ['stock', 'price' ,'name'];
					$product = $this->products->getById($id);
					foreach ($params as $param)
					{
						if ($item->hasParameter($param))
						{
							$item->setParameter($param, $product->getParameter($param));
						}
					}
				}
			}
			return $item;
		});

		return $this->next($collection);
	}

	private function getByProductId($product_id)
	{
		return $this->productsCollection->search(function($item) use($product_id)
		{
			return $item->getParameter('sku') == $product_id;
		});
	}
}