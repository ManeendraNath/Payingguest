<?php

class Custom_hlepter_class {
    /* Member variables */

    var $title;

    function setTitle($title) {
        $this->title = $title;
    }

    function getTitle() {
        echo $this->title . " <br/>";
    }

    /*     * *******************financialYearFormat: Start ************************ */
    /*
     *  parameter $format_type must be either 'short','medium','long'
     * short => xx-xx
     * medium => xxxx-xx
     * long => xxxx-xxxx

     * parameter $financial_year must be in xxxx_xxxx format
     */

    public function financialYearFormat($financial_year = null, $format_type = null) {

        $financial_year_array = explode('_', $financial_year);
        //print_r($financial_year_array);die;
        switch (strtolower($format_type)) {
            case "short":
                $year_from = substr($financial_year_array[0], -2);
                $year_to = substr($financial_year_array[1], -2);
                $financial_year_new_format = $year_from . "-" . $year_to;
                break;
            case "medium":
                $year_from = $financial_year_array[0];
                $year_to = substr($financial_year_array[1], -2);
                $financial_year_new_format = $year_from . "-" . $year_to;
                break;
            case "long":
                $year_from = $financial_year_array[0];
                $year_to = $financial_year_array[1];
                $financial_year_new_format = $year_from . "-" . $year_to;
                break;
            default:
                $financial_year_new_format = $financial_year;
        }
        return $financial_year_new_format;
    }

    /*     * *******************financialYearFormat: End ************************ */


    /*     * *******************currencyData: End ************************ */
    /*
     * This function will convert data in locale indian format. eg. 1234567890 will be 1,23,45,67,890
     */

    function getNumberFormat($data = null) {
        setlocale(LC_MONETARY, 'en_IN');
        $new_data = money_format('%!i', $data);
        return $new_data;
    }

    /*     * *******************currencyData: End ************************ */

    function getCurrencyFormat($value = null) {
        $length = strlen($value);
        switch (true) {
            case ($length == 1):
                $finalval = $value;
                $format = " ";
                break;
            case ($length == 2):
                $finalval = $value;
                $format = " ";
                break;
            case ($length == 3):
                $finalval = $value;
                $format = " ";
                break;
            case ($length == 4):

                $finalval = $value;
                $format = " ";
                break;
            case ($length == 5):
                $finalval = $value;
                $format = " ";
                break;
            case ($length == 6 || $length == 7):
                $val = $value / 100000;
                //$val = round($val, 2);
                $finalval = round($val, 2);
                $format = " Lakh";
                break;

            case ($length >= 8):
                $val = $value / 10000000;
                //$val = round($val, 2);
                $finalval = round($val, 2);
                $format = "Cr+";
                break;

            default:
                $finalval = $value;
                break;
        }
        $formated_value = $this->getNumberFormat($finalval) . ' ' . $format;
        return $formated_value;
        // }
    }

    function getCurrencyShortFormat($value = null, $format_type = null) {
        switch (true) {
            case ($format_type == 'H'):
                $val = $value / 100;
                $finalval = round($val, 2);
                $format = "Hundreds";
                break;

            case ($format_type == 'T'):
                $val = $value / 1000;
                $finalval = round($val, 2);
                $format = " Thousands";
                break;

            case ($format_type == 'L'):
                $val = $value / 100000;
                $finalval = round($val, 2);
                $format = " Lakh+";
                break;

            case ($format_type == 'cr'):
                $val = $value / 10000000;
                $finalval = round($val, 2);
                $format = " Cr+";
                break;
            default:
                $finalval = $value;
                break;
        }
        //$formated_value = $this->getNumberFormat($finalval, $format_type) . ' ' . $format;
        $formated_value = $this->getNumberFormat($finalval, $format_type);
        return $formated_value;
    }

}
