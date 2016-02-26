<?php
class DisconnectAction extends CAction
{
    public function run()
    {
        $res = array( "result" => false , "msg" => Yii::t("common","Something went wrong!" ));
        if(@$_POST){
	        $childId=$_POST["childId"];
	        $childType=$_POST["childType"];
	        $parentId=$_POST["parentId"];
	        $parentType=$_POST["parentType"];
	        $connectType=$_POST["connectType"];
	        $removeMeAsAdmin=false;
			try {
				if($parentType==Organization::COLLECTION)
					$parentConnect="memberOf";
				else if($connectType == "followers")
					$parentConnect="follows";
				else
					$parentConnect=$parentType;
				$data=Link::disconnect($childId, $childType, $parentId, $parentType,Yii::app()->session['userId'], $parentConnect);
				Link::disconnect($parentId, $parentType, $childId, $childType,Yii::app()->session['userId'], $connectType);
				if($childId == Yii::app()->session["userId"]){
					$removeMeAsAdmin=true;
				}
				$res = array( "result" => true , "msg" => Yii::t("",$connectType." successfully removed"), "collection" => $childType,"removeMeAsAdmin"=> $removeMeAsAdmin,"parentId"=>$parentId,"parentType"=>$parentId,"parentEntity"=>$data["parentEntity"]);			
			} catch (CTKException $e) {
				$res = array( "result" => false , "msg" => $e->getMessage() );
			}
		} 
		return Rest::json($res);
    }
}