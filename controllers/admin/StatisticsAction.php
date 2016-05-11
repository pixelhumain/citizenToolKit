<?php

class StatisticsAction extends CAction
{
    public function run()
    {

      $controller = $this->getController();


      $params = array();

      //Get Statistics User
      $res = PHDB::find('stats');
      $citoyens = array();
      foreach ($res as $oneStat) {
        if(isset($oneStat['global']['citoyens'])){
          $citoyens['citoyensAbs'][] = date('Y-m-d H:i:s', $oneStat['created']->sec);
          $citoyens['citoyensOrd'][] = $oneStat['global']['citoyens']['total'];
        }
      }
      $params['citoyens'] = $citoyens;
      
      $page =  "statistics";

      if(Yii::app()->request->isAjaxRequest){
        echo $controller->renderPartial($page,$params,true);
      }
      else {
        $controller->render($page,$params);
      }
    }
}

?>