<?php

class GetLastestReadingAction extends CAction {
/**
*
*/

    public function run() { 

    	$controller=$this->getController();

    	//echo "Index thing\n";
        //var_dump(Thing::getAllValueSCKDevices());

        $params = array();
        if(Yii::app()->request->isAjaxRequest)
            echo $controller->renderPartial("scklastestreadings",$params,true);
        else 
            $controller->render("scklastestreadings",$params);


    }
    



}
?>