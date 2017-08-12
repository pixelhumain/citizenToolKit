<?php
class CrudCollectionAction extends CAction
{
    public function run($action="new")
    {
        $res = array( "result" => false , "msg" => Yii::t("common","Something went wrong!") );
        if( !Person::logguedAndValid() )
            return array("result"=>false, "msg"=>Yii::t("common","Please Login First") );
        else{	
			try {
				if($action == "delete")
					$res = Collection::deleteDocument($_POST["targetId"],$_POST["targetType"],@$_POST['name'],$_POST["colType"],$_POST["docType"]);
				else if($action == "update")
					$res = Collection::updateDocument($_POST["targetId"],$_POST["targetType"], @$_POST['oldname'],@$_POST['name'],$_POST["colType"],$_POST["docType"]);
				else
					$res = Collection::createDocument($_POST["targetId"],$_POST["targetType"], @$_POST['name'],$_POST["colType"],$_POST["docType"]);
			} catch (CTKException $e) {
				$res = array( "result" => false , "msg" => $e->getMessage() );
			}
		}
		return Rest::json($res);
    }
}