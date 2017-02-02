<?php

function xmlheader() {
	return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
}



function pudlExcel($result, $filename, $headers=false) {
	$zip = new ZipArchive();

	@unlink($filename);
	if ($zip->open($filename, ZIPARCHIVE::CREATE) !== true) {
		return 'Cannot open file: ' . $filename;
	}

	$zip->addFromString('[Content_Types].xml', xmlheader().'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Override PartName="/xl/theme/theme1.xml" ContentType="application/vnd.openxmlformats-officedocument.theme+xml"/><Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/docProps/app.xml" ContentType="application/vnd.openxmlformats-officedocument.extended-properties+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/><Override PartName="/xl/sharedStrings.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml"/><Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml"/></Types>');

	$zip->addFromString('_rels/.rels', xmlheader().'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties" Target="docProps/app.xml"/><Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>');

	$zip->addFromString('docProps/app.xml', xmlheader().'<Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties" xmlns:vt="http://schemas.openxmlformats.org/officeDocument/2006/docPropsVTypes"><Application>Microsoft Excel</Application><DocSecurity>0</DocSecurity><ScaleCrop>false</ScaleCrop><HeadingPairs><vt:vector size="2" baseType="variant"><vt:variant><vt:lpstr>Worksheets</vt:lpstr></vt:variant><vt:variant><vt:i4>1</vt:i4></vt:variant></vt:vector></HeadingPairs><TitlesOfParts><vt:vector size="1" baseType="lpstr"><vt:lpstr>Catalog</vt:lpstr></vt:vector></TitlesOfParts><LinksUpToDate>false</LinksUpToDate><SharedDoc>false</SharedDoc><HyperlinksChanged>false</HyperlinksChanged><AppVersion>12.0000</AppVersion></Properties>');

	$zip->addFromString('docProps/core.xml', xmlheader().'<cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:dcmitype="http://purl.org/dc/dcmitype/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"><dc:creator>PHP Universal Database Library</dc:creator><cp:lastModifiedBy>PHP Universal Database Library</cp:lastModifiedBy><dcterms:created xsi:type="dcterms:W3CDTF">'.date('c').'</dcterms:created><dcterms:modified xsi:type="dcterms:W3CDTF">'.date('c').'</dcterms:modified></cp:coreProperties>');

	$zip->addFromString('xl/_rels/workbook.xml.rels', xmlheader().'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/><Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/theme" Target="theme/theme1.xml"/><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/><Relationship Id="rId4" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings" Target="sharedStrings.xml"/></Relationships>');

	$zip->addFromString('xl/styles.xml', xmlheader().'<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><fonts count="1"><font><sz val="11"/><color theme="1"/><name val="Calibri"/><family val="2"/><scheme val="minor"/></font></fonts><fills count="3"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill><fill><patternFill patternType="solid"><fgColor rgb="FF00FF00"/><bgColor indexed="64"/></patternFill></fill></fills><borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders><cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs><cellXfs count="2"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/><xf numFmtId="0" fontId="0" fillId="2" borderId="0" xfId="0" applyFill="1"/></cellXfs><cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles><dxfs count="0"/><tableStyles count="0" defaultTableStyle="TableStyleMedium9" defaultPivotStyle="PivotStyleLight16"/><colors><mruColors><color rgb="FF00FF00"/></mruColors></colors></styleSheet>');

	$zip->addFromString('xl/workbook.xml', xmlheader().'<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><fileVersion appName="xl" lastEdited="4" lowestEdited="4" rupBuild="4507"/><workbookPr defaultThemeVersion="124226"/><bookViews><workbookView xWindow="0" yWindow="120" windowWidth="28755" windowHeight="13095"/></bookViews><sheets><sheet name="Catalog" sheetId="1" r:id="rId1"/></sheets><calcPr calcId="125725"/><fileRecoveryPr repairLoad="1"/></workbook>');

	ob_start();
	echo '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><dimension ref="A1:B6"/><sheetViews><sheetView tabSelected="1" workbookViewId="0"><pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/><selection pane="bottomLeft" activeCell="A1" sqref="A1"/></sheetView></sheetViews><sheetFormatPr defaultRowHeight="15"/><cols><col min="1" max="1" width="12" bestFit="1" customWidth="1"/></cols><sheetData>';

	$x = 1;
	$total = 0;
	$strings = array();
	$colcount = $result->fields();


	//EXPORT HEADERS
	$y = 0;
	$fields = $result->listFields();
	echo '<row r="' . $x . '" spans="1:' . $colcount . '" s="1" customFormat="1">';
	foreach ($fields as $key => $val) {
		$name = $val->name;
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
		echo '<row r="' . $x . '" spans="1:' . $colcount . '">';
		$y = 0;
		foreach ($data as $key => $val) {
			if ($key < 26) {
				$cell = chr(65 + $key) . $x;
			} else {
				$cell = chr(64 + floor($key / 26)) . chr(65 + ($key % 26)) . $x;
			}
			if ($val==='0'  ||  $val==='0.0') {
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
		}
		echo '</row>';
		$x++;
	}

	echo '</sheetData><pageMargins left="0.7" right="0.7" top="0.75" bottom="0.75" header="0.3" footer="0.3"/><pageSetup orientation="portrait" r:id="rId1"/></worksheet>';
	$zip->addFromString('xl/worksheets/sheet1.xml', xmlheader() . ob_get_clean());

	ob_start();
	echo '<sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" ';
	echo 'count="' . $total . '" uniqueCount="' . count($strings) . '">';
	foreach ($strings as $key => $val) {
		echo '<si><t>' . htmlspecialchars($val, ENT_XML1|ENT_SUBSTITUTE, 'UTF-8') . '</t></si>';
	}
	echo '</sst>';
	$zip->addFromString('xl/sharedStrings.xml', xmlheader() . ob_get_clean());

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
