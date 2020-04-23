
<?php  
error_reporting(1);
ini_set("error_reporting","E_ALL");
ini_set("error_display",true);


try{ 
    $client = new 
        SoapClient( 
            "https://164.100.128.140:443/PFMSWebService/Service/DBTBharatPortalService.svc?wsdl", array('soap_version'=>SOAP_1_2) 
        ); 
    $params = array('Username'=>'extsyssvc','Password'=>'cpsms@321!','SchemeCode'=>'B3OGW', 'StateCode'=>'20', 'TransactionDate'=>'Nov-2017'); 
    $webService = $client->GetTransactionSummary($params); 
    $wsResult = $webService->GetTransactionSummaryResponse; 
    print  $wsResult; 
} catch (Exception $e) { 
    print  'Caught exception: '.  $e->getMessage(). "\n"; 
}

?>