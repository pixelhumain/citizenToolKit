<?php
/**
* GetGraphAction 
*  
* @author: Jean Daniel CAZAL <danzalkay551@gmail.com>
* Date: 26/01/2017
* 
*/

class GetGraphAction extends CAction {

    public function run() {

    	//echo "graphe ici";
		$controller=$this->getController();



    	

        $params = array();






        if(Yii::app()->request->isAjaxRequest)
            echo $controller->renderPartial("graph",$params,true);
        else 
            $controller->render("graph",$params);

    }
}

?>
		