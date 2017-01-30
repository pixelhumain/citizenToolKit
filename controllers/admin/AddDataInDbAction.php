<?php
class AddDataInDbAction extends CAction
{
    public function run(){
    	
    	$controller=$this->getController();
    	$result = Import::addDataInDb($_POST);
    	
    	Rest::json( $result );
        Yii::app()->end();
    }
}