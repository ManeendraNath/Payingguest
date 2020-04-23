<?php

/*class Zend_View_Helper_Currency{
 
	public function currencyData1($rs = null) {
		setlocale(LC_MONETARY, 'en_IN');
		$amount = money_format('%!i', $rs);
		//$amount=explode('.',$amount); //Comment this if you want amount value to be 1,00,000.00
		return $amount;
	}
	
	
}*/

class Zend_View_Helper_Currency extends Zend_View_Helper_Abstract 
{
	public function currencyData1($rs = null) {
		setlocale(LC_MONETARY, 'en_IN');
		$amount = money_format('%!i', $rs);
		//$amount=explode('.',$amount); //Comment this if you want amount value to be 1,00,000.00
		return $amount;
	}
}