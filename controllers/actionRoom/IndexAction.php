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

        $where = array("created"=>array('$exists'=>1) ) ;
        if(isset($type))
        	$where["parentType"] = $type;
        if(isset($id))
        	$where["parentId"] = $id;

        if( $type == Person::COLLECTION )
            $roomsActions = Person::getActionRoomsByPersonId($id);
        else if( isset( Yii::app()->session['userId'] ))
            $roomsActions = Person::getActionRoomsByPersonIdByType( Yii::app()->session['userId'] ,$type ,$id );
        else 
            $rooms = ActionRoom::getWhereSortLimit( $where, array("date"=>1), 15);

        $actionHistory = array();
        if( isset($roomsActions) && isset($roomsActions["rooms"]) && isset($roomsActions["actions"])  ){
            $rooms   = $roomsActions["rooms"];
            $actionHistory = $roomsActions["actions"];
        }
        
        //error_log("count rooms : ".count($rooms));

        $discussions = array();
        $votes = array();
        $actions = array();
        foreach ($rooms as $e) 
        { 
            if( in_array($e["type"], array(ActionRoom::TYPE_DISCUSS, ActionRoom::TYPE_FRAMAPAD) )  ){
                array_push($discussions, $e);
            }
            else if ( $e["type"] == ActionRoom::TYPE_VOTE ){
                array_push($votes, $e);
            } else if ( $e["type"] == ActionRoom::TYPE_ACTIONS ){
                array_push($actions, $e);
            }
        }

        $params = array(    "discussions" => $discussions, 
                            "votes" => $votes, 
                            "actions" => $actions, 
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
                echo $controller->renderPartial("index" , $params,true);
            }
        }
        else{
            $controller->render( "index" , $params );
        }
    }
}