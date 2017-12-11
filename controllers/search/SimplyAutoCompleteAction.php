<?php
class SimplyAutoCompleteAction extends CAction
{
    public function run($filter = null){

  		if(empty($_POST["search"]) && empty($_POST["locality"]) && empty($_POST["sourceKey"])) {
        	$res = array();
        }else{
        	$res = Search::networkAutoComplete($_POST, $filter);
        }
	  	Rest::json($res);
		Yii::app()->end();
    }
}
