<?php

class GetLastestReadingAction extends CAction {
/**
*
*/

    public function run($device=4151) { 

    	$controller=$this->getController();

    	//echo "Index thing\n";
        //var_dump(Thing::getAllValueSCKDevices());
        $params = array();
        $params['device']=$device;

        if(Yii::app()->request->isAjaxRequest)
            echo $controller->renderPartial("scklastestreadings",$params,true);
        else 
            $controller->render("scklastestreadings",$params);


    }
    



}
?>