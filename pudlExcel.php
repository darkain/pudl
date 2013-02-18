<?php

function pudlExcel($list, $filename) {
	global $site;
	
	if (is_a($list, 'pudlResult')) {
		$list = $list->rows();
	}
	
	if (!is_array($list)) {
		return false;
	}
	

	define('PHPEXCEL_ROOT', '');
	
	require_once('PHPExcel/Autoloader.php');
	require_once('PHPExcel/PHPExcel.php');
	require_once('PHPExcel/Writer/Excel2007.php');

	$excel = new PHPExcel();
	$excel->setActiveSheetIndex(0);
	$excel->getProperties()->setCreator($site['title']);

	$sheet = $excel->getActiveSheet();
	$sheet->freezePane('A2');

	$excel->getActiveSheet()->setTitle('Price Sheet');


	$i = 0;
	$item = reset($list);
	foreach ($item as $key => &$val) {
		if ($i < 26) {
			$cell = chr(65 + $i) . '1';
		} else {
			$cell = chr(64 + floor($i / 26)) . chr(65 + ($i % 26)) . '1';
		}
		$sheet->SetCellValue($cell, $key);

		$style = $sheet->getStyle($cell);
		$style->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('0000FF00');
		$style->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
		$style->getFont()->setBold(true);

		$i++;
	}


	$row = 2;	
	foreach ($list as $item) {
		$i = 0;
		foreach ($item as $key => &$val) {
			if ($i < 26) {
				$cell = chr(65 + $i) . $row;
			} else {
				$cell = chr(64 + floor($i / 26)) . chr(65 + ($i % 26)) . $row;
			}
			$sheet->SetCellValue($cell, $val);
			$style = $sheet->getStyle($cell);

			if ($key === 'price'  ||  $key === 'list'  ||  $key === 'cost'  ||  $key === 'part_cost') {
				$style->getNumberFormat()->setFormatCode('$ #,##0.00');
			}
			
			if ($key === 'part_number'  ||  $key === 'part_description'  ||  $key === 'part_information'  ||  $key === 'part_inactive_text') {
				$style->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
			}
			
			$i++;
		}
		$row++;
	}


	$item = reset($list);
	$i = 0;
	foreach ($item as $key => &$val) {
		if ($i < 26) {
			$cell = chr(65 + $i);
		} else {
			$cell = chr(64 + floor($i / 26)) . chr(65 + ($i % 26));
		}
		$sheet->getColumnDimension($cell)->setAutoSize(true);
		$i++;
	}


	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header('Content-Disposition: attachment;filename="' . $filename . '"');
	header('Cache-Control: max-age=0');

	$writer = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
	$writer->save('php://output');
	
	return true;
}
