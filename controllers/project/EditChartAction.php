<?php
class EditChartAction extends CAction
{
    public function run() {
		$controller=$this->getController();
		$parentId=$_POST["parentId"];
		//echo $idProject;
		if(!empty($_POST["properties"]))
			$newProperties=$_POST["properties"];
		else
			$newProperties=[];
		/*if(!empty($newProperties)){
			foreach ($newProperties as $data){
				$propertiesList[$data["label"]]=$data["value"];
			}
		}*/

		if (!empty($newProperties)){
        	$res = Project::saveChart($parentId,$newProperties);
        }
        else
        	$res = Project::removeChart($parentId);

  		echo json_encode(array("result"=>true, "properties"=>$newProperties, "msg"=>"Ce projet a de nouvelle propriétés"));
        exit;
	}
}