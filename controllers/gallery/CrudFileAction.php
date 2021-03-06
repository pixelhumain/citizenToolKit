<?php
class CrudFileAction extends CAction
{
    public function run($action="new")
    {
        $res = array( "result" => false , "msg" => Yii::t("common","Something went wrong!") );
        if( !Person::logguedAndValid() )
            return array("result"=>false, "msg"=>Yii::t("common","Please Login First") );
        else{	
			try {
				if($action == "delete")
					$res = Document::delete( @$_POST['name'],null,true);
				else if($action == "update")
					$res = Document::update( @$_POST['oldname'],@$_POST['name']);
				else if($action=="move")
					$res = Folder::moveToFolder($_POST["ids"],$_POST["folderId"]);
			} catch (CTKException $e) {
				$res = array( "result" => false , "msg" => $e->getMessage() );
			}
		}
		return Rest::json($res);
    }
}