<?php

class ModerateAction extends CAction
{
    public function run( $id=null )
    {

      $controller = $this->getController();


      $params = array();
      if(isset($_REQUEST['all'])){
        $page =  "moderateAll";

        $params["news"] = News::getNewsToModerate();
        $params["comments"] =  Comment::getCommentsToModerate();

        //we moderate comments which is part of a news already moderate isAnabuse == true
        if(isset($params["comments"]) && is_array($params["comments"]))foreach($params["comments"] as $key => $val){
          $tmp = News::getById($val['contextId']);
          if(isset($tmp)){
            if(isset($tmp['moderate'])){
              if(isset($tmp['moderate']['isAnAbuse']) && $tmp['moderate']['isAnAbuse'] == true){
                unset($params["comments"][$key]);
              }
            }
          }
        }
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