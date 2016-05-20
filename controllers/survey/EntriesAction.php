<?php
class EntriesAction extends CAction
{
    public function run($id)
    {
      $controller=$this->getController();
      $where = array( "type"=>Survey::TYPE_ENTRY, "survey"=>$id );

      //check if is moderated in which the proper filter will be added to the where clause
      $moduleId = "communecter";//$this->moduleId
      $app = PHDB::findOne (PHType::TYPE_APPLICATIONS, array("key" => $moduleId  ) );
      $isModerator = Survey::isModerator(Yii::app()->session["userId"], $moduleId);

      if(!$isModerator && isset($app["moderation"]))
        $where['applications.'.$moduleId.'.'.Survey::STATUS_CLEARED] = array('$exists'=>false);

      $list = PHDB::find(Survey::COLLECTION, $where );
      $survey = PHDB::findOne (Survey::PARENT_COLLECTION, array("_id"=>new MongoId ( $id ) ) );
      $where["survey"] = $survey;

      $uniqueVoters = PHDB::count( Person::COLLECTION, array("applications.survey"=>array('$exists'=>true)) );


      
      $parentType = ($survey["parentType"] == "organizations") ? "organization" : "";
      if( $parentType == "" )
        $parentType = ($survey["parentType"] == "projects") ? "project" : "";
      if( $parentType == "" )
        $parentType = ($survey["parentType"] == "person") ? "person" : "";

      $surveyLoadByHash = ( isset( $survey["parentType"] ) && isset( $survey["parentId"] ) ) ? "#".$parentType.".detail.id.".$survey["parentId"] : "#rooms"; 

     
      $parent = array("name"=>"_");
      //error_log("parentType : ".$survey["parentType"]);
      if( $survey["parentType"] == Organization::COLLECTION ) {
        $parent = Organization::getById($survey["parentId"]);
      }
      if( $survey["parentType"] == Person::CONTROLLER ) {
        $parent = Person::getById($survey["parentId"]);
      }
      if( $survey["parentType"] == Project::COLLECTION ) {
        $parent = Project::getById($survey["parentId"]);
      }

      $canParticipate = Authorisation::canParticipate( Yii::app()->session['userId'], $survey["parentType"], $survey["parentId"] );

      $tpl = ( isset($_GET['tpl']) ) ? $_GET['tpl'] : "index";

      $controller->renderPartial( $tpl, array( "list" => $list,
                                             "where"=>$where,
                                             "isModerator"=>$isModerator,
                                             "uniqueVoters"=>$uniqueVoters,
                                             "parent"=>$parent,
                                             "parentType" => $survey["parentType"],
                                             "parentId" => $survey["parentId"],
                                             "canParticipate"=>$canParticipate,
                                             "surveyLoadByHash" => $surveyLoadByHash
                                              )  );
    }
}

