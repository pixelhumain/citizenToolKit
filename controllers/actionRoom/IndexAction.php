<?php
class IndexAction extends CAction
{

    //http://127.0.0.1/ph/communecter/rooms/index/type/citoyens/id/xxxxxx
    public function run( $type=null, $id= null, $view=null )
    {
        error_log("room index Action ".$type);
        $controller=$this->getController();
        
        $controller->title = "Action Rooms";
        $controller->subTitle = "Rooms to think, talk, decide in a group";
        $controller->pageTitle = "Communecter - Action Rooms";
        //$controller->toolbarMBZ = array();
        
        $nameParentTitle = "";
        $parent = null;
        if( $type == Project::COLLECTION ) 
            $parent = Project::getById($id);
        else if( $type == Person::COLLECTION ) 
            $parent = Person::getById($id);
        else if( $type == Organization::COLLECTION ) 
            $parent = Organization::getById($id);
        else if( $type == Event::COLLECTION ) 
            $parent = Event::getById($id);
        else if( $type == City::COLLECTION ) {
            $parent = City::getByUnikey($id);
        }
                
    
        if($parent != null && isset($parent['name'])){
            error_log("test ok ? ".$parent['name']);
            $nameParentTitle = $parent['name'];
        }
        else{
            throw new CTKException("Impossible to find this DDA");
        }

        //gère l'activation du DDA sur project et orga
        if((!isset($parent["modules"]) || !in_array("survey", $parent["modules"])) 
            && $type != City::COLLECTION 
            && $type != Person::COLLECTION ){ 
            echo $controller->renderPartial("../pod/roomsList" , 
                                            array(  "empty"=>true, 
                                                    "parent" => $parent, 
                                                    "parentId" => $id, 
                                                    "parentType" => $type,
                                                    "type"=>$type,
                                                    ), true);
            return;
        }

        $urlParams = ( isset($type) && isset($id)) ? "/type/".$type."/id/".$id : "";
        //array_push( $controller->toolbarMBZ, '<a href="#" onclick="openSubView(\'Add a Room\', \'/communecter/rooms/editroom'.$urlParams.'\',null,function(){editRoomSV ();})" title="proposer une " ><i class="fa fa-plus"></i> Room </a>');

        $rooms = ActionRoom::getAllRoomsByTypeId($type, $id);
        $discussions = $rooms["discussions"];
        $votes = $rooms["votes"];
        $actions = $rooms["actions"];
        $history = $rooms["history"];

        $params = array(    "discussions" => $discussions, 
                            "votes" => $votes, 
                            "actions" => $actions, 
                            "history" => $history, 
                            "nameParentTitle" => $nameParentTitle, 
                            "parent" => $parent, 
                            "parentId" => $id, 
                            "parentType" => $type );

        if( isset($actionHistory) )
            $params["history"] = $actionHistory;

		if(Yii::app()->request->isAjaxRequest){
            if($view == "pod"){
                echo $controller->renderPartial("../pod/roomsList" , $params, true);
            }else{
                echo $controller->renderPartial("../dda/index" , $params,true);
            }
        }
        else{
            $controller->render( "index" , $params );
        }
    }
}