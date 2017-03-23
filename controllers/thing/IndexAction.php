<?php

/* @author: Jean Daniel CAZAL <danzalkay551@gmail.com>
* Date: 26/01/2017
* 
*/

class IndexAction extends CAction
{
    public function run()
    {
        $controller=$this->getController();

    	//echo "Index thing\n";
        //var_dump(Thing::getAllValueSCKDevices());

        $params = array();
        //if(Yii::app()->request->isAjaxRequest)
            echo $controller->renderPartial("index",$params,true);
        //else 
          //  $controller->render("index",$params);
        

    }
}