<?php
class FilterAction extends CAction
{
    public function run($id, $type)
    {
        $res = array( "result" => false , "msg" => Yii::t("common","Something went wrong!") );
        $where=array("parentId"=>$id, "parentType"=>$type, "tags"=>array('$in'=>array($_POST["tag"])));
		$res=Bookmark::getListByWhere($where);
      	return Rest::json($res);
    }
}