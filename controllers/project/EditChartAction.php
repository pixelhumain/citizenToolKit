<?php
class EditChartAction extends CAction
{
    public function run() {
		$controller=$this->getController();
		$parentId=$_POST["key"];
		//echo $idProject;
		if(!empty($_POST["description"]) || !empty($_POST["value"]))
			$newProperties=$_POST["chart"];
		else
			$newProperties=[];
		$propertiesList=[];
		if(!empty($newProperties)){
			foreach ($newProperties as $data){
				$propertiesList[$data["label"]]=$data["value"];
			}
		}

		if (!empty($newProperties)){
        	$res = Project::saveChart($parentId,$propertiesList);
        }
        else
        	$res = Project::removeChart($parentId);

  		echo json_encode(array("result"=>true, "properties"=>$propertiesList, "msg"=>"Ce projet a de nouvelle propriétés"));
        exit;
	}
}