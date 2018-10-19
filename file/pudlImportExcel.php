<?php


require_once(is_owner(__DIR__.'/pudlImport.php'));



class			pudlImportExcel
	extends		pudlImport {




	////////////////////////////////////////////////////////////////////////////
	//CONSTRUCTOR
	////////////////////////////////////////////////////////////////////////////
	function __construct(pudl $pudl, $type=false) {
		parent::__construct($pudl, $type);
	}




	////////////////////////////////////////////////////////////////////////////
	//PARSE THE XLSX/ZIP FILE
	////////////////////////////////////////////////////////////////////////////
	public function parse($filename=false) {
		//OPEN XLSX/ZIP FILE
		if (!empty($filename)) {
			if (!$this->_openfile($filename)) return false;
		}


		//LOOP THROUGH EACH ROW AND READ CONTENTS
		foreach ($this->sheet->sheetData->row as $value) {
			$row		= (int) $value->attributes()->r;
			$columns	= count($value->c);

			//IF TRANSLATE IS TRUE, WE USE COLUMN LETTERS, NOT HEADER ROW
			if ($this->translate !== true) {

				//FIRST ROW, HEADER!
				if ($row === 1) {
					$this->_header($value, $columns);
					continue;
				}

				//IF WE DON'T HAVE A HEADER ROW, WE CANNOT DO ANYTHING!
				if (empty($this->header)) continue;
			}

			//BODY CONTENTS
			$this->_body($value, $columns);
		}


		//PROCESS THE FILE
		return $this->importing();
	}




	////////////////////////////////////////////////////////////////////////////
	//OPEN ZIP FILE AND READ CONTENTS
	////////////////////////////////////////////////////////////////////////////
	protected function _openfile($filename) {
		$this->filename = $filename;

		$zip = zip_open(realpath($filename));

		if (!is_resource($zip)) {
			throw new pudlException(
				$this->pudl,
				'CANNOT OPEN XLSX FILE - ' . $filename
			);
		}

		while ($entry = zip_read($zip)) {
			$file = zip_entry_name($entry);

			if ($file === 'xl/sharedStrings.xml') {
				$strings = simplexml_load_string(
					zip_entry_read($entry, zip_entry_filesize($entry)),
					'SimpleXMLElement',
					LIBXML_COMPACT|LIBXML_NOBLANKS|LIBXML_NOCDATA|LIBXML_NONET
				);

			} else if ($file === 'xl/worksheets/sheet1.xml'  &&  empty($this->sheet)) {
				$this->sheet = simplexml_load_string(
					zip_entry_read($entry, zip_entry_filesize($entry)),
					'SimpleXMLElement',
					LIBXML_COMPACT|LIBXML_NOBLANKS|LIBXML_NOCDATA|LIBXML_NONET
				);
			}
		}

		zip_close($zip);

		//VERIFY WE COULD READ THE MAIN SHEET DATA
		if (empty($this->sheet)) {
			throw new pudlException(
				$this->pudl,
				'CANNOT READ XLSX SHEET - ' . $filename
			);
		}

		//VERIFY WE COULD READ THE STRINGS LOOKUP TABLE
		if (empty($strings)  ||  empty($strings->si)) {
			throw new pudlException(
				$this->pudl,
				'CANNOT READ XLSX STRINGS - ' . $filename
			);
		}

		//OPTIMIZE STRINGS TABLE
		foreach ($strings->si as $item) {
			if (isset($item->t)) {
				$this->strings[] = trim(preg_replace('/\s\s+/', ' ', (string)$item->t));

			} else if (isset($item->r)) {
				$string = '';
				foreach ($item->r as $part) $string .= $part->t;
				$this->strings[] = trim(preg_replace('/\s\s+/', ' ', $string));

			} else {
				$this->strings[] = '';
			}
		}

		return true;
	}




	////////////////////////////////////////////////////////////////////////////
	//PARSE THE FIRST ROW / HEADER ROW
	////////////////////////////////////////////////////////////////////////////
	protected function _header($row, $columns) {
		for ($i=0; $i<$columns; $i++) {
			$item = $row->c[$i];

			if (!isset($item->attributes()->r)) continue;
			if (!isset($item->v)) continue;

			$column	= preg_replace('/\d/', '', $item->attributes()->r);
			$name	= $this->strings[(int)$item->v];
			$header	= $this->_translate($name);

			switch (true) {
				case $header === false:
					throw new pudlException(
						$this->pudl,
						'UNKNOWN HEADER: ' . $name
					);
				break;

				case $header === true:
					$this->header[$column] = $name;
				break;

				case is_string($header):
					$this->header[$column] = $header;
				break;
			}
		}
	}




	////////////////////////////////////////////////////////////////////////////
	//PARSE THE BODY CONTENTS
	////////////////////////////////////////////////////////////////////////////
	protected function _body($row, $columns) {
		$data = [];

		for ($i=0; $i<$columns; $i++) {
			$item = $row->c[$i];
			$attr = $item->attributes();

			if (empty($attr->r)) continue;

			$rownum	= (int) preg_replace('/\D/', '', $attr->r);
			$column	= preg_replace('/\d/', '', $attr->r);
			$type	= $attr->t;
			$value	= $item->v;


			//HEADER IS SAME AS COLUMN
			if ($this->translate === true) {
				$header = $column;

			//NO HEADER DATA FOUND
			} else if (empty($this->header[$column])) {
				continue;

			//HEADER FROM TRANSLATION TABLE
			} else {
				$header = $this->header[$column];
			}


			//PULL DATA FROM STRINGS TABLE
			if (!is_null($type)  &&  !is_null($value)) {
				$data[$header] = $this->strings[(int)$value];
				continue;
			}


			//EMPTY CELL, IGNORE IT
			if (!is_null($type)  ||  is_null($value)) continue;


			//VALUE IS SELF-CONTAINED, SO CLEAN IT UP
			$value = trim((string)$value);


			//EMPTY STRING (OR ALL WHITE-SPACE), MEANING EMPTY CELL, IGNORE IT
			if (strlen($value) < 1) continue;


			//FLOATING POINT VALUE
			if (is_numeric($value)  &&
				strpos($value, 'e') === false  &&
				strpos($value, '.') > 0) {

				$data[$header] = round((float)$value, $this->precision);


			//INTEGER VALUE
			} else if (ctype_digit($value)) {
				$data[$header] = (int)$value;


			//ALL OTHER TYPES, USUALLY TEXT STRINGS
			} else {
				$data[$header] = $value;
			}
		}

		//DID THIS ROW CONTAIN DATA? PUSH IT TO OUR ARRAY!
		if (!empty($data)) $this[$rownum] = $data;
	}




	////////////////////////////////////////////////////////////////////////////
	//LOCAL VARIABLES
	////////////////////////////////////////////////////////////////////////////
	/** @var SimpleXMLElement|false */	protected	$sheet		= false;
	/** @var SimpleXMLElement|false */	protected	$strings	= false;
	/** @var int */						public		$precision	= 2;
}
