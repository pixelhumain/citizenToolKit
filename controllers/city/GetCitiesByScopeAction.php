<?php
/**
 */
class GetCitiesByScopeAction extends CAction
{
	 public function run()
    {
        $params = array('result' => false);
        if(isset($_POST['scopes']))
            $params = City::setCitiesByScope($_POST['scopes']);
        Rest::json($params);
        Yii::app()->end();
    }
}