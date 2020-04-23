<?php
include_once APPLICATION_PATH . '/../library/Customclass/Customclass.php';

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap {

    protected function _initDoctype() {
		
	
    }

    protected function _initRouter() {
		
        $router = Zend_Controller_Front::getInstance()->getRouter();
        $router->addRoute('', new Zend_Controller_Router_Route('', array('controller' => 'index', 'action' => 'index')));
     
    }
	
	


   
  
}
