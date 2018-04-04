<?php

class ParamsAction extends CAction {
/**
* Dashboard Organization
*/
    public function run() {
    	$controller=$this->getController();
		
    	$params = array();


    	if(!empty(Yii::app()->session["userId"])){
    		$params["preferences"] = Preference::getPreferencesByTypeId(Yii::app()->session["userId"], Person::COLLECTION);
			$page = "params";

			if( in_array( Yii::app()->theme->name, array("notragora") ) )
					$page = Yii::app()->theme->name."/params";


			//var_dump($page); exit;
			//$page = "onepage";
			
			if(Yii::app()->request->isAjaxRequest)
	          echo $controller->renderPartial($page,$params,true);
	        else
				$controller->render( $page , $params );
    	}else{
    		$controller->redirect( Yii::app()->createUrl($controller->module->id) );
    	}

    	
    }
}
