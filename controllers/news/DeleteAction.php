<?php
class DeleteAction extends CAction {
    
    public function run($id= null) {
    	//Check if connected
        $controller=$this->getController();
        if( ! Person::logguedAndValid()) {
            $res = array("result"=>false, "msg"=>"You must be loggued to delete a comment");
            return Rest::json( $res );
        } else {
        	$res = News::delete($id, Yii::app()->session["userId"], true);
        	if(@$res["newsUp"] && $_POST["isLive"]){
        		$params=array(
    			"news"=>array( $id=>$res["newsUp"]), 
    			"actionController"=>"save",
    			"canManageNews"=>true,
    			"canPostNews"=>true,
                "nbCol" => 1,
                "pair" => false);
				echo $controller->renderPartial("newsPartialCO2", $params,true);
        	}else
                return Rest::json( $res );
        }
    }
}