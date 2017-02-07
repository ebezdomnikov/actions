<?php namespace Actions\Server;

use Ratchet\ConnectionInterface;
use Askedio\LaravelRatchet\RatchetServer as BaseRatchetServer;

use Excel;

class Server extends BaseRatchetServer
{
	private $docs;

	private $rangeNames;

	private $activeWorkSheets;

	private $cells;

	public function init()
	{

	}

	public function getCell($file, $pColumn, $pRow)
	{
		//        if (
		//            isset($this->cells[$file])
		//            && isset($this->cells[$file][$pColumn])
		//            && isset($this->cells[$file][$pColumn][$pRow])
		//        )
		//            return $this->cells[$file][$pColumn][$pRow];

		$cell = $this->getActiveWorkSheet($file)->getCellByColumnAndRow($pColumn, $pRow);
		$this->cells[$file][$pColumn][$pRow] = $cell;

		return $cell;
	}

	public function getActiveWorkSheet($name)
	{
		$hash = md5($name);
		if (isset($this->activeWorkSheets[$hash]))
			return $this->activeWorkSheets[$hash];

		return null;
	}

	public function setActiveWorkSheet($name, $worksheet)
	{
		$hash = md5($name);

		$this->activeWorkSheets[$hash] = $worksheet;

		return $this->activeWorkSheets[$hash];
	}


	public function getNamedRange($name, $doc)
	{
		$hash = md5($name);

		if (isset($this->rangeNames[$hash]))
			return $this->rangeNames[$hash];

		$this->rangeNames[$hash] = $doc->getNamedRange($name);

		return $this->rangeNames[$hash];
	}


	public function getDoc($filename)
	{
		$hash = md5($filename);

		if (isset($this->docs[$hash]))
			return $this->docs[$hash];

		$this->docs[$hash] = Excel::load(
			$filename,
			null,
			null,
			true // Not use Base Path
		);

		return $this->docs[$hash];
	}

	public function onMessage(ConnectionInterface $conn, $input)
	{
		parent::onMessage($conn, $input);

		$ar = false;

		if (str_contains($input,'xls:'))
		{
			$this->init();

			$start = microtime(true);

			$params = explode(':',$input);
			$cmd = $params[1];
			$file = $params[2];
			$doc = $this->getDoc($file);

			if ($cmd == 'readrange')
			{
				$rangeName = $params[3];
				\PHPExcel_Calculation::getInstance($this->getActiveWorkSheet($file)->getParent())->clearCalculationCache();
				$ar = $this->getActiveWorkSheet($file)->namedRangeToArray($rangeName,null,true,true,true);
			}
			elseif ($cmd == 'readcell')
			{
				$cellRow = $params[3];
				$ar = $this->getActiveWorkSheet($file)->getCell($cellRow)->getCalucatedValue();
			}elseif ($cmd == 'writecell')
			{
				$cellRow = $params[3];
				$cellData = $params[4];
				$ar = $this->getActiveWorkSheet($file)->setCellValue($cellRow,$cellData);
			}
			elseif($cmd == 'writecell2')
			{
				$id = $params[3];
				$name = $params[4];
				$value = $params[5];

				$this->logicActionProducts->update(
					$id,
					$name,
					$value
				);
			}
			elseif($cmd == 'getNamedRange_getRange')
			{
				$rangeName = $params[3];
				$ar = $this->getNamedRange($rangeName, $doc)->getRange();
			}
			elseif ($cmd == 'getWorkSheetIndexByRangeName')
			{
				$rangeName = $params[3];
				$ar = $doc->getIndex($this->getNamedRange($rangeName, $doc)->getWorkSheet());
			}
			elseif ($cmd == 'setActiveSheetIndex')
			{
				$index = $params[3];
				if ( ! ($worksheet = $this->getActiveWorkSheet($file)))
				{
					$worksheet = $doc->setActiveSheetIndex($index);
					$this->setActiveWorkSheet($file, $worksheet);
				}
			}
			elseif ($cmd == 'namedRangeToArray')
			{
				$pNamedRange = $params[3];
				$nullValue = $params[4];
				$calculateFormulas = $params[5];
				$formatData = $params[6];
				$returnCellRef = $params[7];
				\PHPExcel_Calculation::getInstance($this->getActiveWorkSheet($file)->getParent())->clearCalculationCache();
				$ar = $this->getActiveWorkSheet($file)->namedRangeToArray($pNamedRange,$nullValue,$calculateFormulas,$formatData, $returnCellRef);
			}
			elseif($cmd == 'writeRow')
			{
				$cellRow = $params[3];
				$rowArray = unserialize($params[4]);
				$this->getActiveWorkSheet($file)->fromArray($rowArray, ' ', $cellRow);
			}
			elseif($cmd == 'setCellValueByColumnAndRow')
			{
				$pColumn = $params[3];
				$pRow = $params[4];
				$pValue = $params[5];
				$returnCell = $params[6];
				//$this->getCell($file, $pColumn, $pRow)->setValueExplicit($pValue,\PHPExcel_Cell_DataType::TYPE_NUMERIC);
				//$this->getActiveWorkSheet($file)->getCellByColumnAndRow($pColumn, $pRow)->setValueExplicit($pValue,\PHPExcel_Cell_DataType::TYPE_NUMERIC);
				$this->getActiveWorkSheet($file)->setCellValueByColumnAndRow($pColumn,$pRow,$pValue,$returnCell);
			}

			$ar = json_encode($ar,JSON_FORCE_OBJECT);

			$this->send($conn, $ar);
			$this->abort($conn);

			$end = round((microtime(true) - $start) * 1000, 2);

			echo "Time: " . $end . " ms";
		}
		else{
			$this->abort($conn);
		}

		if (!$this->throttled) {
			//			$this->send($conn, 'Hello you.');
			//
			//			$this->sendAll('Hello everyone.');
			//
			//			$this->send($conn, 'Wait, I don\'t know you! Bye bye!');






		}
	}
}
