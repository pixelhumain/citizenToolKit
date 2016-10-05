<?php
class AddDataInDbAction extends CAction
{
    public function run(){
    	
    	$controller=$this->getController();
    	$result = NewImport::addDataInDb($_POST);
    	
    	Rest::json( $result );
        Yii::app()->end();
    }
}