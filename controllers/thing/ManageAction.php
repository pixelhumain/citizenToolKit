<?php
/**
* ManageAction 
*  
* @author: Jean Daniel CAZAL <danzalkay551@gmail.com>
* Date: 10/03/2017
* 
*/

class ManageAction extends CAction {

	public function run($country="RE" ){

		$controller=$this->getController();

        $params=array();  
        if(isset($country)){$params['country']=$country; }

		if(Yii::app()->request->isAjaxRequest)
            echo $controller->renderPartial("manage",$params,true);
        else 
            $controller->render("manage",$params);

    }
}

?>