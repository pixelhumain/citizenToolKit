<?php
class CrudAction extends CAction
{
    public function run($action="new")
    {
        $res = array( "result" => false , "msg" => Yii::t("common","Something went wrong!") );
        if( !Person::logguedAndValid() )
            return array("result"=>false, "msg"=>Yii::t("common","Please Login First") );
        else{	
			try {
				if($action == "delete")
					$res = Folder::delete( @$_POST['folderId']);
				else if($action == "update")
					$res = Folder::update( @$_POST['folderId'],@$_POST['name']);
				else if($action == "new")
					$res = Folder::create(@$_POST['targetId'], @$_POST['targetType'], @$_POST['name'],@$_POST['docType'], @$_POST['folderId']);
				else if($action == "move")
					$res = Folder::moveToFolder($_POST["ids"],$_POST["folderId"], $_POST["idsType"]);
					//$res = Folder::moveToFolder([$_POST["id"]],$_POST["folderId"], Folder::COLLECTION);
			} catch (CTKException $e) {
				$res = array( "result" => false , "msg" => $e->getMessage() );
			}
		}
		return Rest::json($res);
    }
}