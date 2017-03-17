<?php
class GlobalAutoCompleteAction extends CAction
{
    public function run($filter = null){
        $res = Search::globalAutoComplete($_POST, $filter);
        if(@$_POST['tpl'])
            echo $this->getController()->renderPartial($_POST['tpl'], array("result"=>$res));
        else
            Rest::json($res);
        Yii::app()->end();
    }
}