<?php
class CheckGeoCodageAction extends CAction
{
    public function run()
    {
        $controller = $this->getController();
    	$params = array();
    	$url = "http://en.wikifur.com/w/images/f/f3/Troll.png" ;
    	Document::getImageByUrl($url, "Troll.png");
        if(Yii::app()->request->isAjaxRequest)
            echo $controller->renderPartial("checkgeocodage",$params,true);
        else 
            $controller->render("checkgeocodage",$params);
    }
}

?>