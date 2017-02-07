<?php namespace Actions\Server;

use Excel;

class ExcelHandle
{
	private $docs;

	private $rangeNames;

	private $activeWorkSheets;

	private $protectedCells;

	private $cells;

	/**
	 * @param $params
	 *
	 * @return null
	 */
	public function proc($params)
	{
		$result = null;

		$params     = explode(':', $params);
		$cmd        = $params[1];
		$session_id = $params[2];
		$user_id    = $params[3];
		$file       = $params[4];
		$hash       = md5($session_id . $user_id);

		if ($doc = $this->getDoc($hash, $file))
		{

			if ($cmd == 'readrange')
			{
				$rangeName = $params[5];
				\PHPExcel_Calculation::getInstance($this->getActiveWorkSheet($file)->getParent())->clearCalculationCache();
				$result = $this->getActiveWorkSheet($file)->namedRangeToArray($rangeName, null, true, true, true);
			}
			elseif ($cmd == 'readcell')
			{
				$cellRow = $params[5];
				$result  = $this->getActiveWorkSheet($file)->getCell($cellRow)->getCalucatedValue();
			}
			elseif ($cmd == 'writecell')
			{
				$cellRow  = $params[5];
				$cellData = $params[6];
				$result   = $this->getActiveWorkSheet($file)->setCellValue($cellRow, $cellData);
			}
			elseif ($cmd == 'writecell2')
			{
				$id    = $params[5];
				$name  = $params[6];
				$value = $params[7];

				$this->logicActionProducts->update(
					$id,
					$name,
					$value
				);
			}
			elseif ($cmd == 'getNamedRange_getRange')
			{
				$rangeName = $params[5];
				$result    = $this->getNamedRange($rangeName, $doc)->getRange();
			}
			elseif ($cmd == 'getWorkSheetIndexByRangeName')
			{
				$rangeName = $params[5];
				$result    = $doc->getIndex($this->getNamedRange($rangeName, $doc)->getWorkSheet());
			}
			elseif ($cmd == 'setActiveSheetIndex')
			{
				$index = $params[5];
				if (!($worksheet = $this->getActiveWorkSheet($file)))
				{
					$worksheet = $doc->setActiveSheetIndex($index);
					$this->setActiveWorkSheet($file, $worksheet);
				}
			}
			elseif ($cmd == 'namedRangeToArray')
			{
				$pNamedRange       = $params[5];
				$nullValue         = $params[6];
				$calculateFormulas = $params[7];
				$formatData        = $params[8];
				$returnCellRef     = $params[9];
				\PHPExcel_Calculation::getInstance($this->getActiveWorkSheet($file)->getParent())->clearCalculationCache();
				$result = $this->getActiveWorkSheet($file)->namedRangeToArray($pNamedRange, $nullValue, $calculateFormulas, $formatData, $returnCellRef);
			}
			elseif ($cmd == 'writeRow')
			{
				$cellRow  = $params[5];
				$rowArray = unserialize($params[6]);
				$this->getActiveWorkSheet($file)->fromArray($rowArray, ' ', $cellRow);
			}
			elseif ($cmd == 'setCellValueByColumnAndRow')
			{
				$pColumn    = $params[5];
				$pRow       = $params[6];
				$pValue     = $params[7];
				$returnCell = $params[8];
				//$this->getCell($file, $pColumn, $pRow)->setValueExplicit($pValue,\PHPExcel_Cell_DataType::TYPE_NUMERIC);
				//$this->getActiveWorkSheet($file)->getCellByColumnAndRow($pColumn, $pRow)->setValueExplicit($pValue,\PHPExcel_Cell_DataType::TYPE_NUMERIC);
				$this->getActiveWorkSheet($file)->setCellValueByColumnAndRow($pColumn, $pRow, $pValue, $returnCell);
			}
			elseif ($cmd == 'isCellProtected')
			{
				$pCoord = $params[5];
				return $this->isCellProtected($file, $pCoord);
				//$result = ($this->getActiveWorkSheet($file)->getStyle($pCoord)->getProtection()->getLocked() !== 'unprotected');

			}
		}

		return $result;
	}

	private function isCellProtected($file, $pCoord)
	{
		if (empty($this->protectedCells[$file]))
		{
			$colStartIndex = $this->getStartColumnIndex(
				$pCoord
			);
			$colIndex = $colStartIndex;

			$endRowIndex = $this->getActiveWorkSheet($file)->getHighestRow();

			for($rowIterator = $colIndex; $rowIterator<$endRowIndex; $rowIterator++)
			{
				$cellName = $this->stringFromColumnIndex($colIndex);
				$isProtected  = ($this->getActiveWorkSheet($file)->getStyle($cellName)->getProtection()->getLocked() !== 'unprotected');
				$this->protectedCells[$file][$cellName]['protected'] = $isProtected;
			}
		}

		if(isset($this->protectedCells[$file][$pCoord]))
		{
			return $this->protectedCells[$file][$pCoord];
		}

		return false;
	}

	/**
	 * Возвращает букву столбца по его номеру
	 * @param int $pColumnIndex
	 *
	 * @return string
	 */
	private function stringFromColumnIndex($pColumnIndex = 0)
	{
		return \PHPExcel_Cell::stringFromColumnIndex($pColumnIndex);
	}

	/**
	 * Индекс первого столбца в промежутке
	 * @param $pRange
	 *
	 * @return int
	 */
	private function getStartColumnIndex($pRange)
	{
		$result = 1;
		$rangeBoundaries = $this->getRangeBoundaries($pRange);
		if (
			count($rangeBoundaries) == 2
			&& count($rangeBoundaries[0]) == 2
			&& count($rangeBoundaries[1]) == 2
		) {
			$result = $rangeBoundaries[0][0];
		}

		return $result;
	}

	/**
	 * @param $pRange
	 *
	 * @return array
	 */
	private function getRangeBoundaries($pRange)
	{
		return \PHPExcel_Cell::rangeBoundaries(
			$pRange
		);
	}
	/**
	 * @param $hash
	 * @param $filename
	 *
	 * @return bool
	 */
	private function getDoc($hash, $filename)
	{
		if (isset($this->docs[$hash]))
			return $this->docs[$hash];

		try
		{
			$this->docs[$hash] = Excel::load(
				$filename,
				null,
				null,
				true // Not use Base Path
			);
		}
		catch (\Exception $e)
		{
			return false;
		}

		return $this->docs[$hash];
	}

	/**
	 * @param $name
	 * @param $doc
	 *
	 * @return mixed
	 */
	private function getNamedRange($name, $doc)
	{
		$hash = md5($name);

		if (isset($this->rangeNames[$hash]))
			return $this->rangeNames[$hash];

		$this->rangeNames[$hash] = $doc->getNamedRange($name);

		return $this->rangeNames[$hash];
	}

	/**
	 * @param $name
	 * @param $worksheet
	 *
	 * @return mixed
	 */
	private function setActiveWorkSheet($name, $worksheet)
	{
		$hash = md5($name);

		$this->activeWorkSheets[$hash] = $worksheet;

		return $this->activeWorkSheets[$hash];
	}

	private function getCell($file, $pColumn, $pRow)
	{
		$cell = $this->getActiveWorkSheet($file)->getCellByColumnAndRow($pColumn, $pRow);
		$this->cells[$file][$pColumn][$pRow] = $cell;

		return $cell;
	}

	private function getActiveWorkSheet($name)
	{
		$hash = md5($name);
		if (isset($this->activeWorkSheets[$hash]))
			return $this->activeWorkSheets[$hash];

		return null;
	}
}