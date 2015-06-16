<?php
class EditChartAction extends CAction
{
    public function run() {
		$controller=$this->getController();
		$newProperties=$_POST;
        $res = Project::saveChart($newProperties);
		echo json_encode(array("result"=>true, "properties"=>$newProperties, "msg"=>"Ce projet a de nouvelle propriétés"));
        exit;
	}
}