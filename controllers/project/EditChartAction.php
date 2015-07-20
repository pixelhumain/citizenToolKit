<?php
class EditChartAction extends CAction
{
    public function run() {
		$controller=$this->getController();
		$idProject=$_GET["id"];
		//echo $idProject;
		$newProperties=$_POST["chart"];
		$propertiesList=[];
		foreach ($newProperties as $data){
			$propertiesList[$data["label"]]=$data["value"];
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
        $res = Project::saveChart( $idProject,$propertiesList );
		echo json_encode(array("result"=>true, "properties"=>$propertiesList, "msg"=>"Ce projet a de nouvelle propriétés"));
        exit;
	}
}