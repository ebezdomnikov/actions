<?php namespace Actions\Repository\Collections\Actions;

use stdClass;
use Traversable;

class Item implements \IteratorAggregate
{
	private $actionItem;

	private $items = [];

	public function __construct($item)
	{
		$this->actionItem = $item;
	}

	public function __call($name, $arguments)
	{
		if ($name == 'getId')
		{
			return $this->actionItem['id'];
		}
		elseif ($name == 'getParentId')
		{
			return $this->actionItem['parent_id'];
		}
		elseif ($name == 'getParentId')
		{
			return $this->actionItem['parent_id'];
		}
		elseif ($name == 'getName')
		{
			return $this->actionItem['name'];
		}
		elseif ($name == 'getBannerUrl')
		{
			return $this->actionItem['assets']['banner']['url'];
		}
        elseif ($name == 'getUrl')
        {
            return $this->actionItem['url'];
        }
        elseif ($name == 'getFilePath')
        {
            return isset($this->actionItem['assets']['file']['dst'])?$this->actionItem['assets']['file']['dst']:'';
        }
        elseif ($name == 'getAssetsPaths')
        {
            $paths = [];

            foreach ($this->actionItem['assets'] as $asset)
            {
                if (isset($asset['src']) && isset($asset['dst']))
                {
                    $path = new StdClass;
                    $path->src = $asset['src'];
                    $path->dst = $asset['dst'];
                    $paths[] = $path;
                }
            }

            if ($this->items)
            {
                foreach ($this->items as $item)
                {
                    $paths = array_merge($paths, $item->getAssetsPaths());
                }
            }

            return $paths;
        }

    }

	public function isExists($searchItem)
	{
		//dump($searchItem);
//		dump($this->items);
//		collect($this->items)->search(function ($value, $key) use($searchItem) {
//			//return ($key == 'id_logic') && $value == $searchItem->getId()
//			dump($value);
//			return false;
//		});
        return false;
	}

	public function add($item)
	{
		if ( ! $this->isExists($item))
			$this->items[] = $item;
	}

	/**
	 *
	 * @return mixed
	 */
	public function getIterator()
	{
	    if (empty($this->items))
	        $this->items = [];

		return new \ArrayIterator($this->items);
	}
}

/**
 * array:6 [▼
"id" => 26731
"parent_id" => null
"url" => "http://172.16.0.115:8000/action/26731"
"name" => "Action 2 - head"
"assets" => array:2 [▼
"file" => null
"banner" => array:3 [▼
"src" => "/home/bezdomnikov-ev/Dropbox/Dev/Projects/beautyportal/portal/remote/actions/2.JPG"
"url" => "http://172.16.0.115:8000/actions/26731/banner/26731.jpg"
"dst" => "/home/bezdomnikov-ev/Dropbox/Dev/Projects/beautyportal/portal/public/actions/26731/banner/26731.jpg"
]
]
"typeclient" => "[143],[187]"
]

 */