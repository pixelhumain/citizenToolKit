<?php 
class GetThumbPathAction extends CAction {
    public function run($id=null,$type=null) {
        $controller=$this->getController();
        $el = Element::getByTypeAndId($type,$id);
		Rest::json(array(
			"profilImageUrl" => $el['profilImageUrl'],
			"profilThumbImageUrl" => $el["profilThumbImageUrl"],
			"profilMarkerImageUrl" => $el["profilMarkerImageUrl"],)); 
    	Yii::app()->end();
    }
}
?>