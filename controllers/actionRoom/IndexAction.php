<?php
class IndexAction extends CAction
{

    //http://127.0.0.1/ph/communecter/rooms/index/type/citoyens/id/xxxxxx
    public function run( $type=null, $id= null )
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
        
        if($parent)
            $nameParentTitle = $parent['name'];

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

        if( isset($roomsActions) && isset($roomsActions["rooms"]) && isset($roomsActions["actions"])  ){
            $rooms   = $roomsActions["rooms"];
            $actions = $roomsActions["actions"];
        }
        
        error_log("count rooms : ".count($rooms));

        $params = array(    "rooms" => $rooms, 
                            "nameParentTitle" => $nameParentTitle, 
                            "parent" => $parent, 
                            "parentId" => $id, 
                            "parentType" => $type );

        if( isset($actions) )
            $params["actions"] = $actions;

		if(Yii::app()->request->isAjaxRequest)
	        echo $controller->renderPartial("index" , $params,true);
	    else
  			$controller->render( "index" , $params );
    }
}