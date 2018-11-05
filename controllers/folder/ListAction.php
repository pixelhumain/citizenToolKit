<?php
class ListAction extends CAction
{
    public function run()
    {
    	$controller = $this->getController();
        $res = array( "result" => false , "msg" => Yii::t("common","Something went wrong!") );
        if( !Person::logguedAndValid() )
            return array("result"=>false, "msg"=>Yii::t("common","Please Login First") );
        else{	
			try {
				if(@$_POST["children"] && $_POST["children"]){
					if(@$_POST["parentId"] && !in_array($_POST["parentId"], ["album", "file"]))
						$folders=Folder::getSubfoldersById($_POST["parentId"]);
					else
						$folders=Folder::getSubfoldersByContext( $_POST["contextId"], $_POST["contextType"], $_POST["docType"]);
				}
				else
					$folders=Folder::getParentsFoldersById($_POST["id"]);
				$res=array("result" => true, "folders"=> $folders);
				//$res = Folder::get(@$_POST['contextId'], @$_POST['contextType'], @$_POST['folderType'], @$_POST['parentId'] );
			} catch (CTKException $e) {
				$res = array( "result" => false , "msg" => $e->getMessage() );
			}
		}
		return Rest::json($res);
    }
}