<?php
class AddDataInDbAction extends CAction
{
    public function run()
    {
    	$array = Import::addDataInDb($_POST);
    	
    	Rest::json( $array );
        Yii::app()->end();
    }
}