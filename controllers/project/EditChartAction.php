<?php
class EditChartAction extends CAction
{
    public function run() {
		$controller=$this->getController();
		$idProject=$_POST["id"];
		//echo $idProject;
		if(!empty($_POST["chart"]))
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
        	$res = Project::saveChart($idProject,$propertiesList);
        }
        else
        	$res = Project::removeChart($idProject);

  		echo json_encode(array("result"=>true, "properties"=>$propertiesList, "msg"=>"Ce projet a de nouvelle propriétés"));
        exit;
	}
}