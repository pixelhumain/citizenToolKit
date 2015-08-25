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
		//print_r($propertiesList);
		/*$propertiesList=array(
							"gouvernance" => $properties["gouvernance"],
							"local" => $properties["local"],	
							"partenaire" => $properties["partenaire"],
							"partage" => $properties["partage"],
							"solidaire" => $properties["solidaire"],
							"avancement" => $properties["avancement"],
		);*/
		if (!empty($newProperties)){
        	$res = Project::saveChart($idProject,$propertiesList);
        }
        else
        	$res = Project::removeChart($idProject);

        //if(!empty($propertiesList)){
	      //  $tabPropeties=$propertiesList;
        //}
        //else()
		echo json_encode(array("result"=>true, "properties"=>$propertiesList, "msg"=>"Ce projet a de nouvelle propriétés"));
        exit;
	}
}