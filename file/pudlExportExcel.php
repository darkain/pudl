<?php


require_once(__DIR__.'/../pudlInterfaces.php');


function pudlExportExcel(pudlData $result, $filename, $headers=false) {
	$zip = new ZipArchive();

	@unlink($filename);
	if ($zip->open($filename, ZIPARCHIVE::CREATE) !== true) {
		return 'Cannot open file: ' . $filename;
	}


	//ADD XML FILES UNMODIFIED
	$zip->addFile(__DIR__.'/content_type.xml',	'[Content_Types].xml');
	$zip->addFile(__DIR__.'/rels.xml',			'_rels/.rels');
	$zip->addFile(__DIR__.'/workbook.xml',		'xl/workbook.xml');
	$zip->addFile(__DIR__.'/workbook.xml.rels',	'xl/_rels/workbook.xml.rels');
	$zip->addFile(__DIR__.'/styles.xml',		'xl/styles.xml');

	//ADD XML AND REPLACE FILENAME
	$zip->addFromString('docProps/app.xml', str_replace(
		'[FILENAME]', $filename,
		file_get_contents(__DIR__.'/app.xml')
	));

	//ADD XML AND REPLACE DATE/TIME
	$zip->addFromString('docProps/core.xml', str_replace(
		'[DATE]', date('c'),
		file_get_contents(__DIR__.'/core.xml')
	));


	ob_start();
	$x			= 1;
	$total		= 0;
	$strings	= [];
	$colcount	= $result->fields();

	//EXPORT HEADERS
	$y = 0;
	$fields = $result->listFields();
	echo "\n\t\t".'<row r="' . $x . '" spans="1:' . $colcount . '" s="1" customFormat="1">';
	foreach ($fields as $key => $val) {
		$name = is_object($val) ? $val->name : $val['name'];
		if (!empty($headers[$name])) $name = $headers[$name];
		$total++;
		$index = array_search($name, $strings, true);
		if ($index === false) {
			$strings[] = $name;
			$index = count($strings) - 1;
		}
		if ($key < 26) {
			$cell = chr(65 + $key) . $x;
		} else {
			$cell = chr(64 + floor($key / 26)) . chr(65 + ($key % 26)) . $x;
		}
		echo '<c r="' . $cell . '" s="1" t="s"><v>' . $index . '</v></c>';
	}
	echo '</row>';


	//EXPORT CELL DATA
	$x++;
	while ($data = $result->row(PUDL_NUMBER)) {
		echo "\n\t\t".'<row r="' . $x . '" spans="1:' . $colcount . '">';
		$y = 0;
		foreach ($data as $key => $val) {
			if ($key < 26) {
				$cell = chr(65 + $key) . $x;
			} else {
				$cell = chr(64 + floor($key / 26)) . chr(65 + ($key % 26)) . $x;
			}

			if ($val === NULL) {
				//OUTPUT NOTHING
			} else if ($val==='0'  ||  $val==='0.0') {
				echo '<c r="' . $cell . '"><v>' . $val . '</v></c>';
			} else if (preg_match('/^[1-9][0-9]{0,12}\.?[0-9]{0,10}$/', $val)) {
				echo '<c r="' . $cell . '"><v>' . $val . '</v></c>';
			} else if (preg_match('/^[0-9]\.[0-9]{0,10}$/', $val)) {
				echo '<c r="' . $cell . '"><v>' . $val . '</v></c>';
			} else if (!empty($val)) {
				$total++;
				$index = array_search($val, $strings, true);
				if ($index === false) {
					$strings[] = $val;
					$index = count($strings) - 1;
				}
				echo '<c r="' . $cell . '" t="s"><v>' . $index . '</v></c>';
			}
			//ELSE OUTPUT NOTHING
		}
		echo '</row>';
		$x++;
	}


	$zip->addFromString(
		'xl/worksheets/sheet1.xml',
		str_replace(
			'[DATA]',
			ob_get_clean(),
			file_get_contents(__DIR__.'/sheet.xml')
		)
	);



	ob_start();

	echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
	echo '<sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" ';
	echo 'count="' . $total . '" uniqueCount="' . count($strings) . '">';

	foreach ($strings as $string) {
		$string = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $string);
		$string = @iconv('UTF-8', 'UTF-8//TRANSLIT', $string);
		$string = htmlspecialchars($string, ENT_XML1|ENT_SUBSTITUTE, 'UTF-8');
		echo "\n\t<si><t>" . $string . '</t></si>';
	}
	echo "\n</sst>";

	$zip->addFromString('xl/sharedStrings.xml', ob_get_clean());




	$zip->close();

	header('Content-Description: File Transfer');
	header('Content-Type: application/octet-stream');
	header('Content-Disposition: attachment; filename='.basename($filename));
	header('Content-Transfer-Encoding: binary');
	header('Expires: 0');
	header('Cache-Control: must-revalidate');
	header('Pragma: public');
	header('Content-Length: ' . filesize($filename));

	readfile($filename);
	@unlink($filename);
}
