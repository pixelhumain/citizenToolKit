<?php
class EditChartAction extends CAction
{
    public function run() {
		$controller=$this->getController();
		$parentId=$_POST["parentId"];
		$parentId=$_POST["parentId"];
		//echo $idProject;
		if(!empty($_POST["properties"])){
			if(@$_POST["properties"]["commons"]){
				$newProperties=$_POST["properties"]["commons"];
				$label="commons";
			}else if(@$_POST["properties"]["open"]){
				$newProperties=$_POST["properties"]["open"];
				$label="open";
			}
			
		}else
			$newProperties=[];
		/*if(!empty($newProperties)){
			foreach ($newProperties as $data){
				$propertiesList[$data["label"]]=$data["value"];
			}
		}*/

		if (!empty($newProperties)){
        	$res = Project::saveChart($parentId,$newProperties,$label);
        }
        else
        	$res = Project::removeChart($parentId,$label);

  		echo json_encode(array("result"=>true, "properties"=>$newProperties, "msg"=>"Ce projet a de nouvelle propriétés"));
        exit;
	}
}