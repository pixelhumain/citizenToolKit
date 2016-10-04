<?php
class AddDataInDbAction extends CAction
{
    public function run(){
    	
    	$controller=$this->getController();
    	$array = NewImport::addDataInDb($_POST, $controller->moduleId);
    	
    	Rest::json( $array );
        Yii::app()->end();
    }
}