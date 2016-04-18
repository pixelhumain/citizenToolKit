<?php
class EntriesAction extends CAction
{
    public function run($id)
    {
      $controller=$this->getController();
      $where = array( "type"=>Survey::TYPE_ENTRY, "survey"=>$id );

      //check if is moderated in which the proper filter will be added to the where clause
      $moduleId = "pppm";//$this->moduleId
      $app = PHDB::findOne (PHType::TYPE_APPLICATIONS, array("key" => $moduleId  ) );
      $isModerator = Survey::isModerator(Yii::app()->session["userId"], $moduleId);

      if(!$isModerator && isset($app["moderation"]))
        $where['applications.'.$moduleId.'.'.Survey::STATUS_CLEARED] = array('$exists'=>false);

      $list = PHDB::find(Survey::COLLECTION, $where );
      $survey = PHDB::findOne (Survey::PARENT_COLLECTION, array("_id"=>new MongoId ( $id ) ) );
      $where["survey"] = $survey;

      $uniqueVoters = PHDB::count( Person::COLLECTION, array("applications.survey"=>array('$exists'=>true)) );

     
      $tpl = ( isset($_GET['tpl']) ) ? $_GET['tpl'] : "index";


      $controller->layout = "//layouts/mainSearch";
      $controller->renderPartial( $tpl, array( "list" => $list,
                                       "where"=>$where,
                                       "isModerator"=>$isModerator,
                                       "uniqueVoters"=>$uniqueVoters )  );
      
    }
}