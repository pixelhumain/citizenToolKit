<?php
class CheckDataImportAction extends CAction
{
    public function run()
    {
    	$array = Import::createArrayList($_POST["list"]);
    	Rest::json( $array );
        Yii::app()->end();
    }
}