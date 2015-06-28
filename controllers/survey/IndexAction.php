<?php
class IndexAction extends CAction
{
    public function run($type=null, $id= null)
    {
        $controller=$this->getController();
    
        $where = array("type"=>Survey::TYPE_SURVEY);

        //check if information is Postal Code restricted 
        if(isset($_GET["cp"]))
          $where["cp"] = $_GET["cp"];
        if( $type )
          $where["parentType"] = $type;
        if( $id )
          $where["parentId"] = $id;
        $parentTitle = "";
        if( $type && $id ){
          $parent = PHDB::findOneById( $type, $id );
          if( isset( $parent["name"] ) ){
            if($type == Organization::COLLECTION)
              $parentCtrler = Organization::CONTROLLER;
            else if($type == Person::COLLECTION)
              $parentCtrler = Person::CONTROLLER;
            $parentTitle = '<a href="'.Yii::app()->createUrl("/communecter/".$parentCtrler."/dashboard/id/".$id).'">'.$parent["name"]."</a>'s ";
          }
        }

        $list = PHDB::find(Survey::PARENT_COLLECTION, $where );
        $user = ( isset( Yii::app()->session["userId"])) ? PHDB::findOne (Person::COLLECTION, array("_id"=>new MongoId ( Yii::app()->session["userId"] ) ) ) : null;
        $uniqueVoters = PHDB::count( Person::COLLECTION, array("applications.survey"=>array('$exists'=>true)) );

        $controller->title = $parentTitle."Surveys";
        $controller->subTitle = "Nombres de votants inscrit : ".$uniqueVoters;
        $controller->pageTitle = "Communecter - Surveys ".$parentTitle;

        $controller->toolbarMBZ = array(
        '<a href="#" class="newSurvey" title="proposer une " ><i class="fa fa-plus"></i> SURVEY </a>',
        );

        $controller->render( "index", array( "list" => $list,
                                      "where"=>$where,
                                      "user"=>$user,
                                      "uniqueVoters"=>$uniqueVoters )  );
    }
}