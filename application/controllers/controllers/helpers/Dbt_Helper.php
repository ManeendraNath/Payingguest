<?php

/**
 * Action Helper for finding days in a month
 */
class Dc_Helper_Custom extends Zend_Controller_Action_Helper_Abstract
{
		/*
		* This function get current financial year
		*/
        public function getCurrentFinancialYear(){
            $curre_year = strtotime(date("d-m-Y"));          
            $fixedyear = strtotime(date("d-m-Y", strtotime("31-03-".date("Y"))));
            if($curre_year > $fixedyear){
                $start = date("Y");
            }else if($curre_year <= $fixedyear){
                $dataa = date("Y")-1;
                $start = $dataa;
            }
                $end_year = $start+1;
                $dateadded = $start."_".$end_year;
                return $dateadded;
        }
	
	/**
     * First entry is for January
     */
    protected $daysInMonth = array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
     
    /**
     * @var Zend_Loader_PluginLoader
     */
    public $pluginLoader;
 
    /**
     * Constructor: initialize plugin loader
     * 
     * @return void
     */
    public function __construct()
    {
        $this->pluginLoader = new Zend_Loader_PluginLoader();
    }
 
    /** 
     * Returns the number of days in a given month + year
     * 
     * @param int $month
     * @param int $year
     * @return int
     * @throws Exception
     */
    public function getDaysInMonth($month, $year)
    {
        if ($month < 1 || $month > 12)
        {
            throw new Exception('Invalid month ' . $month);
        }
    
        $d = $this->daysInMonth[$month - 1];
    
        if ($month == 2)
        {
            // Check for leap year
            // Forget the 4000 rule, I doubt I'll be around then...
         
            if (($year % 4) == 0)
            {
                if (($year % 100) == 0)
                {
                    if (($year % 400) == 0)
                    {
                        $d = 29;
                    }
                }
                else
                {
                    $d = 29;
                }
            }
        }
     
        return $d;
    }
     
    /**
     * Strategy pattern: call helper as broker method
     * 
     * @param  int $month 
     * @param  int $year 
     * @return int
     */
    public function direct($month, $year)
    {
        return $this->getDaysInMonth($month, $year);
    }
}
