<?php
class AddDataInDbAction extends CAction
{
    public function run(){
    	
    	$controller=$this->getController();
    	$array = Import::addDataInDb($_POST, $controller->moduleId);
    	
    	Rest::json( $array );
        Yii::app()->end();
    }
}