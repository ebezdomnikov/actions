<?php namespace Actions\Server;

use Ratchet\ConnectionInterface;
use Askedio\LaravelRatchet\RatchetServer as BaseRatchetServer;



class Server2 extends BaseRatchetServer
{
	private $handlers;

	private $handlersClass = [
		'xls' => ExcelHandle::class,
	    'logic' => LogicHandle::class
	];

	private function sendToHandler($name, $params)
	{
		$start = microtime(true);
		$handler = null;
		$result = null;

		if (isset($this->handlers[$name]))
			$handler = $this->handlers[$name];
		else
		{
			$this->handlers[$name] = new $this->handlersClass[$name];
			$handler = $this->handlers[$name];
		}

		if (! empty($handler))
			$result =  $handler->proc($params);

		$end = round((microtime(true) - $start) * 1000, 2);

		if (env('APP_DEBUG') || env('APP_ENV'))
		{
			echo "Executed time: " . $end . " sec. \n";
		}

		return $result;
	}



	public function onMessage(ConnectionInterface $conn, $input)
	{
		parent::onMessage($conn, $input);

		if ($input == 'alive')
		{
			$this->send($conn, 'yes');
			$this->abort($conn);
		}
		elseif (str_contains($input,'logic:'))
		{
			$result = $this->sendToHandler('logic',$input);

			$result = json_encode($result,JSON_FORCE_OBJECT);
			$this->send($conn, $result);
			$this->abort($conn);
		}
		elseif (str_contains($input,'xls:'))
		{
			$result = $this->sendToHandler('xls', $input);

			$result = json_encode($result, JSON_FORCE_OBJECT);
			$this->send($conn, $result);
			$this->abort($conn);
		}

		$this->send($conn, 'NODATA');
		$this->abort($conn);


		if (!$this->throttled) {
			//			$this->send($conn, 'Hello you.');
			//
			//			$this->sendAll('Hello everyone.');
			//
			//			$this->send($conn, 'Wait, I don\'t know you! Bye bye!');
		}
	}
}
