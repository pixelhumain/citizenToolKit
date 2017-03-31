<?php
class SaveAction extends CAction {

    public function run() {

	    $controller=$this->getController();
		
	    $mapping = $_POST["mapping"];
	    
	    
	    try{
			if ( Person::logguedAndValid() ) {
				//Save the mapping
				Rest::json(Mapping::insert($mapping, Yii::app()->session["userId"]));
			} else {
				return Rest::json(array("result"=>false, "msg"=>"You are not loggued with a valid user !"));
			}
		} catch (CTKException $e) {
			return Rest::json(array("result"=>false, "msg"=>$e->getMessage()));
		}

		Yii::app()->end();
	}
}

?>