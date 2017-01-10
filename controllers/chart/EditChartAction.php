<?php
class EditChartAction extends CAction
{
    public function run() {
		$controller=$this->getController();
		$id=$_POST["id"];
		$type=$_POST["type"];
		//echo $idProject;
		if(!empty($_POST["properties"]))
			$newProperties=$_POST["properties"];
		else
			$newProperties=[];
		/*$propertiesList=[];
		if(!empty($newProperties)){
			foreach ($newProperties as $data){
				$propertiesList[$data["label"]]=$data["value"];
			}
		}*/

		if (!empty($newProperties)){
        	$res = Element::saveChart($type,$id,$newProperties);
        }
        else
        	$res = Element::removeChart($type,$id);

  		echo json_encode(array("result"=>true, "properties"=>$newProperties, "msg"=>Yii::t("common", "properties well updated")));
        exit;
	}
}