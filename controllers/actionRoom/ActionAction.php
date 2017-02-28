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
      if(!isset($action)) {
          throw new CTKException("Impossible to find this action");
          //return;
      }

      if( !count($pageView) )
      {
        ActStr::viewPage( $controller->id."/".$controller->action->id."/id/".$id );
        //incerment this survey's entry pageView
        PHDB::update ( ActionRoom::COLLECTION_ACTIONS , array("_id" => new MongoId($id)) , array('$inc'=>array( "viewCount" => 1 ) ));
      }

      $room = ActionRoom::getById($action["room"]);
      $params = array( 
            /*"title" => $action["name"] ,
            "content" => $controller->renderPartial( "entry", array( "action" => $action ), true),
            "contentBrut" => $action["message"],*/
            "room" => $room,
            "action" => $action,
             ) ;

      if( isset($action["organizerType"]) )
      {
          if( $action["organizerType"] == Person::COLLECTION )
          {
            $organizer = Person::getById( $action["organizerId"] );
            $params["organizer"] = array(  "name" => $organizer["name"],
                                           "link" => "url.loadByHash('#person.detail.id.".$action["organizerId"]."')");
          }
          else if( $action["organizerType"] == Organization::COLLECTION )
          {
            $organizer = Organization::getById( $action["organizerId"] );
            $params["organizer"] = array(  "name" => $organizer["name"],
                                           "link" => "url.loadByHash('#organization.detail.id.".$action["organizerId"]."')");
          }
          else if( $action["organizerType"] == Project::COLLECTION )
          {
            $organizer = Project::getById( $action["organizerId"] );
            $params["organizer"] = array(  "name" => $organizer["name"],
                                           "link" => "url.loadByHash('#project.detail.id.".$action["organizerId"]."')");
          }

          $params["organizerType"] = $action["organizerType"];
      }

      if( isset($room) )
      { 
         if( $room["parentType"] == Person::COLLECTION )
          {
            $parent = Person::getById( $room["parentId"] );
            $params["parent"] = array(  "name" => $room["name"],
                                           "link" => "url.loadByHash('#person.detail.id.".$room["parentId"]."')");
          }
          else if( $room["parentType"] == Organization::COLLECTION )
          {
            $parent = Organization::getById( $room["parentId"] );
            $params["parent"] = array(  "name" => $parent["name"],
                                           "link" => "url.loadByHash('#organization.detail.id.".$room["parentId"]."')");
          }
          else if( $room["parentType"] == Project::COLLECTION )
          {
            $parent = Project::getById( $room["parentId"] );
            $params["parent"] = array(  "name" => $parent["name"],
                                        "link" => "url.loadByHash('#project.detail.id.".$room["parentId"]."')");
          }
          else if( $room["parentType"] == City::COLLECTION )
          { 
            $parent = City::getByUnikey( $room["parentId"] );
            $params["parent"] = array(  "name" => $parent["name"],
                                        "insee" => $parent["insee"],
                                        "cp" => $parent["cp"],
                                        "link" => "url.loadByHash('#city.detail.insee.".$parent["insee"].".postalCode.".$parent["cp"]."')");
          }


          $params["parentType"] = $room["parentType"];
          if(@$parent["profilImageUrl"]) 
              $params["parent"]["profilImageUrl"] = $parent["profilImageUrl"];
      }

        $params["contributors"] = array();
        $countStrongLinks = 0;
        if(@$action["links"]["contributors"])
        {
            $countStrongLinks=count($action["links"]["contributors"]);
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
		$limit = array(Document::IMG_PROFIL => 1);
		$images = Document::getImagesByKey($id, ActionRoom::COLLECTION_ACTIONS, $limit);
		$params["images"] = $images;
        $params["parentSpace"] = ActionRoom::getById( $action["room"] );
        $params["countStrongLinks"]= $countStrongLinks;
		//$params["countLowLinks"] = @$followers;
      if(Yii::app()->request->isAjaxRequest)
        echo $controller->renderPartial("actionStandalone",$params,true);
      else if( !Yii::app()->request->isAjaxRequest ){
        $controller->layout = "//layouts/mainSearch";
        $controller->render( "actionStandalone", $params );
      } else 
          Rest::json( $params);
    }
}