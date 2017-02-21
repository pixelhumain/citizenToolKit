<?php
class GetInfoAdressByInseeAction extends CAction
{
    public function run()
    {
    	$where = array("insee"=>$_POST["insee"], "cp"=>$_POST["cp"]);
		$fields = array("alternateName");
        $adress = City::getWhere($where, $fields);
    	Rest::json( $adress );
        Yii::app()->end();
    }
}