<?php
class ActionAction extends CAction
{
    public function run($id)
    {
      $controller=$this->getController();
      $action = PHDB::findOne (ActionRoom::COLLECTION_ACTIONS, array("_id"=>new MongoId ( $id ) ) );
     
      $pageView = ActivityStream::getWhere(array("verb"=>ActStr::VERB_VIEW,
                                                 "ip"=>$_SERVER['REMOTE_ADDR'],
                                                 "object.objectType" => ActStr::TYPE_URL,
                                                 "object.id"=>$controller->id."/".$controller->action->id."/id/".$id));

      if( !count($pageView) )
      {
        ActStr::viewPage( $controller->id."/".$controller->action->id."/id/".$id );
        //incerment this survey's entry pageView
        PHDB::update ( ActionRoom::COLLECTION_ACTIONS , array("_id" => new MongoId($id)) , array('$inc'=>array( "viewCount" => 1 ) ));
      }

      $params = array( 
            /*"title" => $action["name"] ,
            "content" => $controller->renderPartial( "entry", array( "action" => $action ), true),
            "contentBrut" => $action["message"],*/
            "room" => ActionRoom::getById($action["room"]),
            "action" => $action,
             ) ;

      if( isset($action["organizerType"]) )
      {
          if( $action["organizerType"] == Person::COLLECTION )
          {
            $organizer = Person::getById( $action["organizerId"] );
            $params["organizer"] = array(  "name" => $organizer["name"],
                                           "link" => "loadByHash('#person.detail.id.".$action["organizerId"]."')");
          }
          else if( $action["organizerType"] == Organization::COLLECTION )
          {
            $organizer = Organization::getById( $action["organizerId"] );
            $params["organizer"] = array(  "name" => $organizer["name"],
                                           "link" => "loadByHash('#organization.detail.id.".$action["organizerId"]."')");
          }
          else if( $action["organizerType"] == Project::COLLECTION )
          {
            $organizer = Project::getById( $action["organizerId"] );
            $params["organizer"] = array(  "name" => $organizer["name"],
                                           "link" => "loadByHash('#project.detail.id.".$action["organizerId"]."')");
          }

          $params["organizerType"] = $action["organizerType"];
      }

      if( isset($action["parentType"]) )
      { 
         if( $action["parentType"] == Person::COLLECTION )
          {
            $parent = Person::getById( $action["parentId"] );
            $params["parent"] = array(  "name" => $parent["name"],
                                           "link" => "loadByHash('#person.detail.id.".$action["parentId"]."')");
          }
          else if( $action["parentType"] == Organization::COLLECTION )
          {
            $parent = Organization::getById( $action["parentId"] );
            $params["parent"] = array(  "name" => $parent["name"],
                                           "link" => "loadByHash('#organization.detail.id.".$action["parentId"]."')");
          }
          else if( $action["parentType"] == Project::COLLECTION )
          {
            $parent = Project::getById( $action["parentId"] );
            $params["parent"] = array(  "name" => $parent["name"],
                                        "link" => "loadByHash('#project.detail.id.".$action["parentId"]."')");
          }
          else if( $action["parentType"] == City::COLLECTION )
          { 
            $parent = City::getByUnikey( $action["parentId"] );
            $params["parent"] = array(  "name" => $parent["name"],
                                        "insee" => $parent["insee"],
                                        "cp" => $parent["cp"],
                                        "link" => "loadByHash('#city.detail.insee.".$parent["insee"].".postalCode.".$parent["cp"]."')");
          }


          $params["parentType"] = $action["parentType"];
      }

        $params["contributors"] = array();
        if(@$action["links"]["contributors"])
        {
            foreach ($action["links"]["contributors"] as $uid => $e) 
            {
                $citoyen = Person::getPublicData($uid);
                if(!empty($citoyen)){
                    $citoyen["type"]=Person::COLLECTION;
                    $profil = Document::getLastImageByKey($uid, Person::COLLECTION, Document::IMG_PROFIL);
                    if($profil !="")
                        $citoyen["imagePath"] = $profil;
                    array_push( $params["contributors"] , $citoyen);
                }
            }
        }

        $params["parentSpace"] = ActionRoom::getById( $action["room"] );

      if(Yii::app()->request->isAjaxRequest)
        echo $controller->renderPartial("actionStandalone",$params,true);
      else if( !Yii::app()->request->isAjaxRequest ){
        $controller->layout = "//layouts/mainSearch";
        $controller->render( "actionStandalone", $params );
      } else 
          Rest::json( $params);
    }
}