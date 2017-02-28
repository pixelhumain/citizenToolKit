 <?php
class IndexAction extends CAction
{
    public function run($type, $id)
    {
        $controller=$this->getController();

        $params = array();

        $res = Comment::buildCommentsTree($id, $type, Yii::app()->session["userId"]);
        $params['comments'] = $res["comments"];
        $params['communitySelectedComments'] = $res["communitySelectedComments"];
        $params['abusedComments'] = $res["abusedComments"];
        
        $params['options'] = $res["options"];
        $params["contextType"] = $type;
        $params["nbComment"] = $res["nbComment"];
        $params['canComment'] = $res["canComment"] ;
		
        if($type == Event::COLLECTION) {
            $params["context"] = Event::getById($id);
        } else if($type == Project::COLLECTION) {
            $params["context"] = Project::getById($id);
        } else if($type == Organization::COLLECTION) {
            $params["context"] = Organization::getById($id);
        } else if($type == Person::COLLECTION) {
            $params["context"] = Person::getById($id);
        } else if($type == News::COLLECTION) {
            $params["context"] = News::getById($id);
		} else if($type == Poi::COLLECTION) {
            $params["context"] = Poi::getById($id);

        } else if($type == Survey::COLLECTION) {
            $params["context"] = Survey::getById($id);
            /*AUTH*/
            $actionRoom = ActionRoom::getById($params["context"]["survey"]);
            $canParticipate = Authorisation::canParticipate(Yii::app()->session["userId"], $actionRoom["parentType"], $actionRoom["parentId"]);
            $canComment = $params["canComment"] && $canParticipate;
            $params['canComment'] = $canComment;

            $params["parentType"] = $actionRoom["parentType"];
            
        } else if($type == ActionRoom::COLLECTION) {
            $actionRoom = ActionRoom::getById($id);
            $params["context"] = $actionRoom;
            //Images
			$limit = array(Document::IMG_PROFIL => 1);
			$images = Document::getImagesByKey($id, ActionRoom::COLLECTION, $limit);
			$params["images"] = $images;

            if($actionRoom["parentType"] == Person::CONTROLLER) 
                $params["parent"] = Person::getById($actionRoom["parentId"]);   
            if($actionRoom["parentType"] == Organization::COLLECTION) 
                $params["parent"] = Organization::getById($actionRoom["parentId"]);   
            if($actionRoom["parentType"] == Project::COLLECTION) 
                $params["parent"] = Project::getById($actionRoom["parentId"]);   
            if($actionRoom["parentType"] == City::COLLECTION) {
                $parent = City::getByUnikey($actionRoom["parentId"]);   
                $params["parent"] = array(  "name" => $parent["name"],
                                        "insee" => $parent["insee"],
                                        "cp" => $parent["cp"],
                                        "link" => "url.loadByHash('#city.detail.insee.".$parent["insee"].".postalCode.".$parent["cp"]."')");
            }

            if(!isset($params["parent"])) {
                throw new CTKException("Impossible to find this actionRoom");
                //return;
            }
            
            $params["parentType"] = $actionRoom["parentType"];
            $params["parentId"] = $actionRoom["parentId"];
            /*AUTH*/
            $canParticipate = Authorisation::canParticipate(Yii::app()->session["userId"], $actionRoom["parentType"], $actionRoom["parentId"]);
            $canComment = $params["canComment"] && $canParticipate;
            $params['canComment'] = $canComment;

        }else if($type == ActionRoom::COLLECTION_ACTIONS) {
            $params["context"] = ActionRoom::getActionById($id);
            /*AUTH*/
            $limit = array(Document::IMG_PROFIL => 1);
			$images = Document::getImagesByKey($id, ActionRoom::COLLECTION_ACTIONS, $limit);
			$params["images"] = $images;
            $actionRoom = ActionRoom::getById($params["context"]["room"]);
            $canParticipate = Authorisation::canParticipate(Yii::app()->session["userId"], $actionRoom["parentType"], $actionRoom["parentId"]);
            $canComment = $params["canComment"] && $canParticipate;
            $params['canComment'] = $canComment;
            $params["parentType"] = $actionRoom["parentType"];
            $params["parentId"] = $actionRoom["parentId"];
        } else if($type == Need::COLLECTION) {
            $params["context"] = Need::getById($id);
        } else if($type == Media::COLLECTION) {
            $params["context"] = Media::getById($id);
        } else {
        	throw new CTKException("Error : the type is unknown ".$type);
        }

        if(@$params["parentType"] == City::COLLECTION) $params['canComment'] = true;

        $params["idComment"] = $id;

        if(Yii::app()->request->isAjaxRequest){
	        if($type != ActionRoom::COLLECTION && $type != ActionRoom::COLLECTION_ACTIONS)
                echo $controller->renderPartial("../comment/commentPodSimple" , $params, true);
            else
                echo $controller->renderPartial("../comment/commentPodActionRooms" , $params, true);
	    }else{
            if($type != ActionRoom::COLLECTION && $type != ActionRoom::COLLECTION_ACTIONS)
                $controller->renderPartial("../comment/commentPod" , $params, true);
            else
                $controller->renderPartial("../comment/commentPodActionRooms" , $params, true);
        }
    }

 
}