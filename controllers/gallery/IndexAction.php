<?php
class IndexAction extends CAction
{
    public function run($id, $type, $docType=null, $contentKey=null, $folderId=null, $tpl=null)
    {
        $controller=$this->getController();
		$params = array();
		$params["itemId"] = $id;
		$params['itemType'] = $type;
		$params["docType"]=$docType;
		//$view="index";
		//if(@$_POST["view"])
		//	$view=$_POST["view"];
		if(@$_POST["contentKey"] || !empty($contentKey))
			$contentKey=(@$_POST["contentKey"]) ? $_POST["contentKey"] : $contentKey;

		if(@$_POST["folderId"])
			$folderId=(@$_POST["folderId"]) ? $_POST["folderId"] : $folderId;
		//if(@$_POST["subDir"])
		//	$subDir=$_POST["subDir"];
		//if(@$_POST["colName"])
		//	$colName=$_POST["colName"];
		//$params["colName"]=@$colName;
		//$params["subDir"]=@$subDir;
		$params["contentKey"]=@$contentKey;
		$params["folderId"]=@$folderId; 
		$params["results"]=array();
		$element=Element::getElementSimpleById($id, $type,null, array(Document::COLLECTION,"preferences"));
		if(@$docType){
			//$indexFiles="files";
			//$indexFolders="folders";
			if(empty($tpl)){
				$params["initGallery"]=true;
				if(@$folderId){
					$currentFold=Folder::getById($folderId);
					$params["currentFolders"]=[$currentFold];
					if(@$currentFold["parentId"])
						$params["currentFolders"]=array_merge($params["currentFolders"], Folder::getParentsFoldersById($currentFold["parentId"]));
				}
			}
			if(!@$contentKey && $docType==Document::DOC_TYPE_IMAGE){
				$where=array("id"=>$id, "type"=>$type, "contentKey"=>"slider", "doctype"=>$docType, "folderId"=> array('$exists'=> false));
				$params["docs"] = Document::getListDocumentsWhere($where,$docType);
				//if($docType=="image"){
					$countImagesProfil=Document::countByWhere($id,$type,"profil");
					$countImagesBanner=Document::countByWhere($id,$type,"banner");
					$countImagesAlbum=Document::countByWhere($id,$type,"slider");
					$thumbProfil=Document::getLastThumb($id,$type,"profil");
					$thumbBanner=Document::getLastThumb($id,$type,"banner");
					$thumbAlbum=Document::getLastThumb($id,$type,"slider");
					$countAlbums=Folder::countSubfoldersByContext($id, $type, $docType);
					$params["folders"]=array();
					$params["folders"]=array(
						"profil"=>array("name"=>Yii::t('common', "profile"), "contentKey" => true, "count"=>$countImagesProfil,"imageThumb"=>@$thumbProfil),
						"banner"=>array("name"=>Yii::t('common', "cover"), "contentKey" => true, "count"=>$countImagesBanner,"imageThumb"=>@$thumbBanner),
						"album"=>array("name"=>Yii::t('common', "album"), "contentKey" => true, "count"=>$countImagesAlbum,"imageThumb"=>@$thumbAlbum,"countFolders"=>@$countAlbums)
				);
			//}
			}else{
				$where=array("id"=>$id, "type"=>$type, "doctype"=>$docType);
				if($docType==Document::DOC_TYPE_IMAGE)
					$where["contentKey"] = $contentKey;
				if(@$folderId)
					$where["folderId"]=$folderId;
				else
					$where["folderId"]=array('$exists'=>false);
				if($docType=="bookmarks"){
					$where=array("parentId"=>$id, "parentType"=>$type);
					$resFiles=Bookmark::getListByWhere($where);
					$params["tagsFilter"]=Bookmark::getListOfTags($where);
				}else{
					$resFiles=Document::getListDocumentsWhere($where,$docType);
				}
				$params["docs"] = $resFiles; 
				$params["folders"]=array();
				if($contentKey=="slider" || $docType=="file"){
					if(@$folderId)
						$subFolders=Folder::getSubfoldersById($folderId);
					else
						$subFolders=Folder::getSubfoldersByContext($id,$type, $docType);
					
					foreach ($subFolders as $key => $value) {
						if($docType=="image")
							$value["imageThumb"]=Document::getLastThumb($id,$type,"slider",$key);
						$value["countFolders"]=Folder::countSubfolders($key);
						$value["count"]=Document::countByWhere($id,$type,$contentKey, $key, $docType);
						$params["folders"][$key]=$value;
						
					}

				}
			}
		}
		if(isset(Yii::app()->session["userId"]))
			$params["editAlbum"] = Authorisation::canParticipate( Yii::app()->session['userId'], $type, $id);

		$params["authorizedToStock"]= Document::authorizedToStock($id, $type,Document::DOC_TYPE_IMAGE);
		$params["edit"] = Authorisation::canEditItem(Yii::app()->session["userId"], $type, $id);
        $params["openEdition"] = Authorisation::isOpenEdition($id, $type, @$element["preferences"]);
        
		$controller->subTitle = "";
		if(@$tpl=="json")
			echo json_encode($params);
		else
			echo $controller->renderPartial("index", $params);
    }
}