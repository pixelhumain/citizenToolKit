<?php

class ModerateAction extends CAction
{
    public function run( $id=null )
    {

      $controller = $this->getController();

      /* **************************************
      *  PERSON
      ***************************************** */
      $controller->title = "Admin Directory Restricted Zone";
      $controller->subTitle = "This is a restricted zone";
      $controller->pageTitle = ucfirst($controller->module->id)." - ".$controller->title;

      /* **************************************
      *  NEWS
      ***************************************** */
      $params["news"] =  PHDB::find('news',array( "reportAbuse"=> array('$exists'=>1)));


      /* **************************************
      *  COMMENTS
      ***************************************** */
      $params["comments"] =  PHDB::find('comments',array( "reportAbuse"=> array('$exists'=>1)));
      // $params["path"] = "../default/";

      //$page = $params["path"]."directoryTable";
      $page =  "moderate";

      if(Yii::app()->request->isAjaxRequest){
        echo $controller->renderPartial($page,$params,true);
      }
      else {
        $controller->render($page,$params);
      }
    }
}

?>