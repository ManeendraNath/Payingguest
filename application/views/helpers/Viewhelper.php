<?php
function currencyData1($rs = null) {
    setlocale(LC_MONETARY, 'en_IN');
   //$amount = money_format('%!i', $rs);
    if ($rs<0) return "-".asDollars(-$rs);
      return number_format($rs, 2);
    //$amount=explode('.',$amount); //Comment this if you want amount value to be 1,00,000.00
    //return $amount;
}

function currencyData($rs = null) {
    setlocale(LC_MONETARY, 'en_IN');

    if ($rs<0) return "-".asDollars(-$rs);
      return number_format($rs, 2);

	//if(is_numeric ($rs)){
   // $amount = money_format('%!i', $rs);
    //$amount = explode('.', $amount); //Comment this if you want amount value to be 1,00,000.00
    //return $amount[0];
	//}else{return $rs;}
}

function amountFormat($amount = null) {
    if ($amount > 9999999) {
        $formatedAmount = currencyData(round($amount / 10000000, 2)) . ' Cr+';
    } else {
        $formatedAmount = currencyData($amount);
    }
    return $formatedAmount;
}

function no_to_words($no) {
    if ($no == 0 || $no == '') {
        $no = 0;
        return $no;
    } else {
        $n = strlen($no); // 7
        //$pow = pow(10,$n);
        switch ($n) {
            case 1:
                $finalval = currencyData($no);
                break;
            case 2:
                $finalval = currencyData($no);
                break;
            case 3:
                // $val = $no/100;
                // $val = round($val, 2);
                $finalval = currencyData($no);
                break;
            case 4:
                // $val = $no/1000;
                // $val = round($val, 2);
                $finalval = currencyData($no);
                break;
            case 5:
                // $val = $no/1000;
                // $val = round($val, 2);
                $finalval = currencyData($no);
                break;
            case 6:
                $val = $no / 100000;
                $val = round($val, 2);
                $finalval = currencyData1($val) . " Lakh";
                break;
            case 7:
                $val = $no / 100000;
                $val = round($val, 2);
                $finalval = currencyData1($val) . " Lakh";
                break;
            case 8:
                $val = $no / 10000000;
                $val = round($val, 2);
                $finalval = currencyData1($val) . " Cr+";
                break;

            default:
                $val = $no / 10000000;
                $val = round($val, 2);
                $finalval = currencyData1($val) . " Cr+";
                break;
        }
        return $finalval;
    }
}

function no_to_words_cr($no) {
    if ($no == 0 || $no == '') {
        $no = 0;
        return $no;
    } else {
        $n = strlen($no); // 7
        //$pow = pow(10,$n);
        switch ($n) {
            case 1:
                $finalval = currencyData($no);
                break;
            case 2:
                $finalval = currencyData($no);
                break;
            case 3:
                // $val = $no/100;
                // $val = round($val, 2);
                $finalval = currencyData($no);
                break;
            case 4:
                // $val = $no/1000;
                // $val = round($val, 2);
                $finalval = currencyData($no);
                break;
            case 5:
                // $val = $no/1000;
                // $val = round($val, 2);
                $finalval = currencyData($no);
                break;
            case 6:
                $val = $no / 100000;
                $val = round($val, 2);
                $finalval = currencyData1($val) . " Lakh";
                break;
            case 7:
                $val = $no / 100000;
                $val = round($val, 2);
                $finalval = currencyData1($val) . " Lakh";
                break;
            case 8:
                $val = $no / 10000000;
                $val = round($val, 2);
                $finalval = currencyData1($val) . " Cr";
                break;

            default:
                $val = $no / 10000000;
                $val = round($val, 2);
                $finalval = currencyData1($val) . " Cr";
                break;
        }
        return $finalval;
    }
}


function no_in_cr($no) {
    if ($no == 0 || $no == '') {
        $no = 0;
        return $no;
    } else {
        //$pow = pow(10,$n);
		$val = $no / 10000000;
		$val = round($val, 2);
		$finalval = currencyData1($val);
        return $finalval;
    }
}

function monthName() {
        $month_names = array(
            "4" => "April",
            "5" => "May",
            "6" => "June",
            "7" => "July",
            "8" => "August",
            "9" => "September",
            "10" => "October",
            "11" => "November",
            "12" => "December",
            "1" => "January",
            "2" => "February",
            "3" => "March",
        );

        return $month_names;
    }