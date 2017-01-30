<?php

class UpdateProfilAction extends CAction
{
	/**
	* Dashboard Organization
	*/
    public function run() { 
    	$controller=$this->getController();
        $params = array();
		$page = "updateProfil";
		if(Yii::app()->request->isAjaxRequest)
            echo $controller->renderPartial($page,$params,true);
        else 
			$controller->render( $page , $params );
    }

    
}
