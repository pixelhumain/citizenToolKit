<?php
class IndexAction extends CAction
{
    public function run($type=null, $id= null)
    {
        $controller=$this->getController();
    
        $where = array("type"=>SurveyType::TYPE_SURVEY);

        //check if information is Postal Code restricted 
        if(isset($_GET["cp"]))
          $where["cp"] = $_GET["cp"];
        if($type)
          $where["parentType"] = $type;
        if($id)
          $where["parentId"] = $id;

        $list = PHDB::find(Action::ACTION_ROOMS, $where );
        $user = ( isset( Yii::app()->session["userId"])) ? PHDB::findOne (PHType::TYPE_CITOYEN, array("_id"=>new MongoId ( Yii::app()->session["userId"] ) ) ) : null;
        $uniqueVoters = PHDB::count( PHType::TYPE_CITOYEN, array("applications.survey"=>array('$exists'=>true)) );

        $controller->title = "Tous les sondages";
        $controller->subTitle = "Nombres de votants inscrit : ".$uniqueVoters;
        $controller->pageTitle = "Communecter - Sondages";

        $controller->render( "index", array( "list" => $list,
                                      "where"=>$where,
                                      "user"=>$user,
                                      "uniqueVoters"=>$uniqueVoters )  );
    }
}