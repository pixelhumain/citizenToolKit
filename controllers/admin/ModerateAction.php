<?php

class ModerateAction extends CAction
{
    public function run( $id=null )
    {

      $controller = $this->getController();


      $params = array();
      if(isset($_REQUEST['all'])){
        $page =  "moderateAll";
        $params["news"] =  PHDB::find('news',array( "reportAbuse"=> array('$exists'=>1), "moderate.isAnAbuse" => array('$exists'=>0)));
        $params["comments"] =  PHDB::find('comments',array( "reportAbuse"=> array('$exists'=>1),"moderate.isAnAbuse" => array('$exists'=>0)));
      }
      elseif(isset($_REQUEST['one'])){
        $page =  "moderateOne"; 
      }
      else{
        $page =  "moderate";
      }

      if(Yii::app()->request->isAjaxRequest){
        echo $controller->renderPartial($page,$params,true);
      }
      else {
        $controller->render($page,$params);
      }
    }
}

?>