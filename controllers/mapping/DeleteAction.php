<?php
class DeleteAction extends CAction {

    public function run() {

	    $controller=$this->getController();
	    $post = $_POST;
	    	    
	    try{
			if ( Person::logguedAndValid() ) {
				//Save the mapping
				Rest::json(Mapping::delete($post, Yii::app()->session["userId"]));
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