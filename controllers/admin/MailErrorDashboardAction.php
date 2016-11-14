<?php 
 /**
  * Display the mail error dashboard
  */
class MailErrorDashboardAction extends CAction {
    public function run() {
        $controller = $this->getController();

      $controller->title = "Admin Mail Error Dashboard - Restricted Zone";
      $controller->subTitle = "Admin Mail Error Dashboard";
      $controller->pageTitle = ucfirst($controller->module->id)." - ".$controller->title;

      /* **************************************
      *  MAIL ERRORS
      ***************************************** */
      $mailErrors = MailError::getMailErrorSince(time() - 60*60*24*7);

      $params["mailErrors"] = $mailErrors;
      $params["path"] = "../admin/";
		  $page = $params["path"]."mailErrorTable";

      if(Yii::app()->request->isAjaxRequest){
        echo $controller->renderPartial($page,$params,true);
      }
      else {
        $controller->render($page,$params);
      }
    }
}
