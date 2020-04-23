<?php

/**
 * array formate to export in excel:
 * $data = array(
  'sec_1' => array(
  'header' => array(
  0 => array('title' => 'Export to Excel'),
  1 => array('0' => 'header1', '1' => 'header2', '2' => 'header3', '3' => 'header4', 'condition' => array('bgcolor' => array('1' => 'B9D0E8', '3' => 'FF0000'))),
  2 => array('1', '2', '3', '4', 'condition' => array('bgcolor' => array('1' => 'B9D0E8', '3' => 'FF0000'))),
  ),
  'data' => array(
  0 => array('sno' => '1', 'name' => 'Dilip', 'age' => '3432', 'condition' => array('color' => array('age' => 'A6A6A6', 'name' => 'A6A6A6'))),
  1 => array('sno' => '2', 'name' => 'dfgfd', 'age' => '123', 'condition' => array('bgcolor' => array('age' => 'D6BFD4', 'name' => 'D3D7CF'))),
  2 => array('sno' => '3', 'name' => 'fff', 'age' => '565', 'condition' => array('bgcolor' => array('age' => 'D6BFD4', 'name' => 'D3D7CF'))),
  3 => array('sno' => '4', 'name' => 'rrrr', 'age' => '88888', 'condition' => array('bgcolor' => array('age' => 'F79494', 'name' => 'CC99FF'))),
  4 => array('sno' => '5', 'name' => 'ttt', 'age' => '999'),
  5 => array('sno' => '6', 'name' => 'yyy', 'age' => '111'),
  )
  ),
  'sec_2' => array(
  'header' => array(
  0 => array('title' => 'Title of the excel'),
  1 => array('header1', 'header2', 'header3', 'header4'),
  2 => array('1', '2', '3', '4'),
  ),
  'data' => array(
  0 => array('sno' => '1', 'name' => 'Dilip', 'age' => '3432'),
  1 => array('sno' => '1', 'name' => 'dfgfd', 'age' => '123'),
  2 => array('sno' => '1', 'name' => 'fff', 'age' => '565'),
  3 => array('sno' => '1', 'name' => 'rrrr', 'age' => '88888'),
  4 => array('sno' => '1', 'name' => 'ttt', 'age' => '999'),
  5 => array('sno' => '1', 'name' => 'yyy', 'age' => '111'),
  )
  ),
  );
 */
class Zend_Controller_Action_Helper_ExportToExcel extends Zend_Controller_Action_Helper_Abstract {

    /**
     * @return object
     * @author Dilip
     * @date 1 Sep 2017
     * @This function set the property of the excel sheet
     */
    public function exportExcelProperty($object) {
        // set default font
        $object->getDefaultStyle()->getFont()->setName('Calibri');
        // set default font size
        $object->getDefaultStyle()->getFont()->setSize(10);
        // create the writer
        $object->getProperties()->setCreator("DBT Bharat");
        $object->getProperties()->setLastModifiedBy("Dilip Kumar");
        $object->getProperties()->setTitle("DBT Bharat Report");
        $object->getProperties()->setSubject("DBT Bharat Report");
        $object->getProperties()->setDescription("DBT Bharat Report");

        return $object;
    }

    /**
     * @return object
     * @author Dilip
     * @date 1 Sep 2017
     * @This function set the property of the excel sheet
     */
    public function exportToExcelData($filename = null, $table_array = array(), $worksheet_name = '', $worksheet_index = null) {


        $realPath = realpath($filename);
        if (false === $realPath) {
            touch($filename);
            chmod($filename, 0777);
        }
        //$filename = realpath($filename);
        $col = 0;
        $savedPrecision = ini_get('precision');
        $objTpl = PHPExcel_IOFactory::load($filename); //echo $filename;die;
        ini_set('precision', $savedPrecision);

        $objTpl = $this->exportExcelProperty($objTpl);
		if($worksheet_index){
			$objTpl->createSheet();
			$objTpl->setActiveSheetIndex($worksheet_index);
		}else{
			$objTpl->setActiveSheetIndex(0);
		}

        $objTpl = $this->exportToExcel1($table_array, $objTpl, $worksheet_name);
        $objWriter = PHPExcel_IOFactory::createWriter($objTpl, 'Excel2007');
        //$objWriter->save($filename);
//        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
//        header('Content-Disposition: attachment;filename="test-' . date('d-m-y') . '.xlsx"');
//        header('Cache-Control: max-age=0');
//        $objWriter->save('php://output');
        ////////// Export to Excel: End ////////////
        return $objWriter;
    }

    public function exportToExcel1($data = array(), $object, $worksheet_name = '') {
        $condition = array();
        $object->getActiveSheet()->setTitle($worksheet_name);
        $object->getDefaultStyle()->getFont()->setName('Georgia');
        $object->getDefaultStyle()->getFont()->setSize(11);

		$center_style = array(
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
			)
		);
		
        $row_count = 1;
        foreach ($data as $key1 => $value1) {
            foreach ($value1 as $key2 => $value2) {
                ///////////Header section starts//////////////
				
                if ($key2 == 'merge') {
                    foreach ($value2 as $key3 => $value3) {
						foreach ($value3 as $key4 => $value4) {
							
							$min_cell = $value4['min_cell'];
							$max_cell = $value4['max_cell'];
							$title = $value4['title'];
							$object->getActiveSheet()->mergeCells($min_cell.':'.$max_cell);
							$object->getActiveSheet()->getCell($min_cell)->setValue($title);

							$condition['bgcolor'][$min_cell] = $value4['condition']['bgcolor'];
							$object = $this->getCustomStyle($object, array('cell_name' => $min_cell, 'data' => $title), $condition);
							$object->getActiveSheet()->getStyle($min_cell.':'.$max_cell)->applyFromArray($center_style);
							
							$condition = '';
						}
						$row_count++;
                    }
                }				
                if ($key2 == 'header') {
                    foreach ($value2 as $key3 => $value3) {
                        $column_count = 0;
                        foreach ($value3 as $key4 => $value4) {

                            if (false === is_array($value4)) {
                                $cell_name = $this->generateCell($value4, $column_count, $row_count);
                                $object->getActiveSheet()->setCellValue($cell_name, $value4);

                                if (is_array($value3['condition']['bgcolor'])) {
                                    if (in_array($key4, array_keys($value3['condition']['bgcolor']))) {
                                        $condition['bgcolor'][$cell_name] = $value3['condition']['bgcolor'][$key4];
                                    }
                                }
                                $object = $this->getCustomStyle($object, array('cell_name' => $cell_name, 'data' => $value4), $condition);

                                $column_count++;
                            }
                        }
                        $row_count++;
                    }
                }

                /////////////End Header Section///////////////
                /////////////Body section Starts/////////////
                if ($key2 == 'data') {
                    foreach ($value2 as $key3 => $value3) {

                        $column_count = 0;
                        foreach ($value3 as $key4 => $value4) {
                            if (false === is_array($value4)) {
                                $cell_name = $this->generateCell($value4, $column_count, $row_count);
                                $object->getActiveSheet()->setCellValue($cell_name, $value4);
                                if (is_array($value3['condition']['bgcolor'])) {
                                    if (in_array($key4, array_keys($value3['condition']['bgcolor']))) {
                                        $condition['bgcolor'][$cell_name] = $value3['condition']['bgcolor'][$key4];
                                    }
                                }

                                if (is_array($value3['condition']['underline'])) {
                                    if (in_array($key4, array_keys($value3['condition']['underline']))) {
                                        $condition['underline'][$cell_name] = $value3['condition']['underline'][$key4];
                                    }
                                }
								
                                if ((strpos($key3, "_schemes_crore") !== false) && is_numeric($value4)) {
									$condition['float'] = 'y';
                                }else{
									$condition['float'] = 'n';
								}

                                $object = $this->getCustomStyle($object, array('cell_name' => $cell_name, 'data' => $value4), $condition);

                                $column_count++;
                            }
                        }
                        $row_count++;
                    }
                }
                /////////////End Body Section///////////////
            }
        }

		$object = $this->finalSheet($object);
        return $object;
    }

    public function generateCell($data = array(), $column_no = '', $row_count = '') {
        $reminder = $column_no % 26;
        $divedend = $column_no / 26;
        if ($column_no > 25) {
            $ord = ord('A') + ($divedend - 1);
        } else {
            $ord = '';
        }
        $val = (ord('A') + $reminder);
        $cell_name = chr($ord) . chr($val) . ($row_count);
        return trim($cell_name);
    }

    public function getCustomStyle($object, $cell = array(), $condition) {

        $cell_name = $cell['cell_name'];
        $cell_value = $cell['data'];
        $cell_color = $condition['bgcolor'];
        $cell_merge = $condition['colspan'];
		$is_float = $condition['float'];
		$cell_underline = $condition['underline'];

		//Set Border Style
        if ($cell_name) {
            $object->getActiveSheet()->getStyle($cell_name)->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
            $object->getActiveSheet()->getStyle($cell_name)->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
            $object->getActiveSheet()->getStyle($cell_name)->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
            $object->getActiveSheet()->getStyle($cell_name)->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
        }

		//Set allign of the column
        if (is_numeric($cell_value)) { //If numeric align it center,else left align

			if ($is_float == 'y') { //If numeric align it center,else left align
				$object->getActiveSheet()->getStyle($cell_name)->getNumberFormat()->setFormatCode('#,##0.00');
			} else {
				$object->getActiveSheet()->getStyle($cell_name)->getNumberFormat()->setFormatCode('#,##0');
			}
			
			unset($is_float);
			
			$object->getActiveSheet()->getStyle($cell_name)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        } else {
			$object->getActiveSheet()->getStyle($cell_name)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        }

        if (is_array($cell_color)) {
            $colored_cell = trim($cell_color[$cell_name]);
            if (!empty($colored_cell)) {
                $object->getActiveSheet()->getStyle($cell_name)->applyFromArray(
                        array(
                            'fill' => array(
                                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                'color' => array('rgb' => $colored_cell)
                            ),
                            'borders' => array(
                                'allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN,
                                    'color' => array('rgb' => PHPExcel_Style_Color::COLOR_BLACK)
                                )
                            )
                        )
                );
            }
        }
		
        if (is_array($cell_underline)) {
            $underlined_cell = trim($cell_underline[$cell_name]);
            if (!empty($underlined_cell)) {
                $object->getActiveSheet()->getStyle($cell_name)->applyFromArray(
                        array(
						  'font' => array(
							'underline' => PHPExcel_Style_Font::UNDERLINE_SINGLE,
							'color' => array('rgb' => $underlined_cell),
						  )
                        )
                );
            }
        }
        return $object;
    }

    public function finalSheet($object) {
        $highestRow = $object->getActiveSheet()->getHighestRow(); //e.g. 18
        $highestColumn = $object->getActiveSheet()->getHighestColumn(); //e.g. E
        $highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn); //e.g., 5

        for ($row = 1; $row < $highestRow + 2; $row++) {

            $object->getActiveSheet()->getStyle('A' . $row . ':' . $highestColumn . $row)->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
            $object->getActiveSheet()->getStyle('A' . $row . ':' . $highestColumn . $row)->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
            $object->getActiveSheet()->getStyle('A' . $row . ':' . $highestColumn . $row)->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
            $object->getActiveSheet()->getStyle('A' . $row . ':' . $highestColumn . $row)->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
        }

        $object->getActiveSheet()->setCellValue('A' . ($highestRow + 4), 'This is System Generated Report as on: ' . date("d/m/Y H:i:s"));
        $object->getActiveSheet()->mergeCells('A' . ($highestRow + 4) . ':' . $highestColumn . ($highestRow + 4));

        return $object;
    }

}
