<?php
class IndexAction extends CAction
{
    public function run($id, $type, $docType="image", $contentKey=null, $subDir=array())
    {
        $controller=$this->getController();
		$params = array();
		$params["itemId"] = $id;
		$params['itemType'] = $type;
		$params["docType"]=$docType;
		$view="index";
		if(@$_POST["view"])
			$view=$_POST["view"];
		if(@$_POST["contentKey"])
			$contentKey=$_POST["contentKey"];
		if(@$_POST["subDir"])
			$subDir=$_POST["subDir"];
		if(@$_POST["colName"])
			$colName=$_POST["colName"];
		$params["colName"]=@$colName;
		$params["subDir"]=@$subDir;
		$params["contentKey"]=@$contentKey; 
		$params["results"]=array();
		$element=Element::getElementSimpleById($id, $type,null, array(Document::COLLECTION,"preferences"));
		$params["collections"]=array();
		if(@$element[Document::COLLECTION] && @$element[Document::COLLECTION][$docType])
			$params["collections"]=$element[Document::COLLECTION][$docType];
		/*if(!empty($element[Document::COLLECTION] && @$element[Document::COLLECTION][$docType])){
			$params["results"]=$element[Document::COLLECTION][$docType];
			if($subDir!=null){
				$params["results"]=Document::getListDocumentsByList($element[Document::COLLECTION][$docType][$subDir],$docType, $subDir);
				if($subDir=="album"){
					foreach($element[Document::COLLECTION][$docType] as $key => $data){
						if(!in_array(["profil,banner,album"], $key))
							$params["results"]["albums"]=array();
							$params["results"]["albums"][$key]=array("count"=>count(data),"getImgPath"=>Document::getDocumentPathById($data[0]));
					}
				}
			}
		}*/
		if(@$contentKey){
			$where=array("id"=>$id, "type"=>$type, "contentKey"=>$contentKey, "doctype"=>Document::DOC_TYPE_IMAGE);
			if(@$colName){
				//$lastKey = count($subDir);
				$where["collection"]=$colName;
			}
			$params['images'] = Document::getListDocumentsWhere($where,$docType);
			$params["albums"]=array();
			if($contentKey=="slider"){
				if(@$element[Document::COLLECTION] && @$element[Document::COLLECTION][$docType]){
					$albums=$element[Document::COLLECTION][$docType];
					if(!empty($subDir)){
						//print_r($subDir);
						//print_r($albums);
						foreach($subDir as $data){
							$albums=$albums[$data];
						}
					}
					foreach($albums as $key => $data){
						if($key!="updated"){
							$params["albums"][$key]=array();
							$alb=0;
							foreach($data as $i => $v){
								if($i!="updated")
									$alb++;
							}
							$params["albums"][$key]=array(
								"count"=>Document::countImageByWhere($id,$type,"slider",$key),"imageThumb"=>Document::getLastThumb($id,$type,"slider",$key), "countAlbums"=>$alb
							);
						}
					}
				}
			}
		}else{
			$where=array("id"=>$id, "type"=>$type, "contentKey"=>"slider", "doctype"=>$docType, "collection"=> array('$exists'=> false));
			$params["images"] = Document::getListDocumentsWhere($where,$docType);
			if($docType=="image"){
				$countImagesProfil=Document::countImageByWhere($id,$type,"profil");
				$countImagesBanner=Document::countImageByWhere($id,$type,"banner");
				$countImagesAlbum=Document::countImageByWhere($id,$type,"slider");
				if($countImagesProfil > 0)	
					$thumbProfil=Document::getLastThumb($id,$type,"profil");
				if($countImagesBanner > 0)	
					$thumbBanner=Document::getLastThumb($id,$type,"banner");
				if($countImagesAlbum > 0)	
					$thumbAlbum=Document::getLastThumb($id,$type,"slider");
				if(@$element[Document::COLLECTION] && @$element[Document::COLLECTION][$docType]){
					$countAlbums=count($element[Document::COLLECTION][$docType]);
				}
				$params["albums"]=array();
				$params["albums"]=array(
					"profil"=>array("count"=>$countImagesProfil,"imageThumb"=>@$thumbProfil),
					"banner"=>array("count"=>$countImagesBanner,"imageThumb"=>@$thumbBanner),
					"album"=>array("count"=>0,"imageThumb"=>@$thumbAlbum,"countAlbums"=>@$countAlbums)
				);
			}
		}
		
		if(isset(Yii::app()->session["userId"]))
			$params["editAlbum"] = Authorisation::canParticipate( Yii::app()->session['userId'], $type, $id);

		//$params['controllerId'] = $controllerId;
		$contentKey=null;
		$params["authorizedToStock"]= Document::authorizedToStock($id, $type,Document::DOC_TYPE_IMAGE);
		$params["edit"] = Authorisation::canEditItem(Yii::app()->session["userId"], $type, $id);
        $params["openEdition"] = Authorisation::isOpenEdition($id, $type, @$element["preferences"]);
        
		$controller->subTitle = "";
		echo $controller->renderPartial($view, $params);
    }
}