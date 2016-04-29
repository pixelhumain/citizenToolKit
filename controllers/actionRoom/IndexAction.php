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

        if( $type == Project::COLLECTION ) {
            //$controller->toolbarMBZ = array("<a href='".Yii::app()->createUrl("/".$controller->module->id."/project/dashboard/id/".$id)."'><i class='fa fa-lightbulb-o'></i>Project</a>");
            $project = Project::getById($id);
            $nameParentTitle = $project["name"];
            $controller->title = $project["name"]."'s Rooms";
            $controller->subTitle = "Every Project thinks, talks & decides.";
            $controller->pageTitle = "Communecter - ".$controller->title;
        } else if( $type == Person::COLLECTION ) {
            //$controller->toolbarMBZ = array("<a href='".Yii::app()->createUrl("/".$controller->module->id."/person/dashboard/id/".$id)."'><i class='fa fa-user'></i>Person</a>");
            $person = Person::getById($id);
            $nameParentTitle = $person["name"];
            $controller->title = $person["name"]."'s Rooms";
            $controller->subTitle = "Everyone thinks, talks & decides.";
            $controller->pageTitle = "Communecter - ".$controller->title;
        } else if( $type == Organization::COLLECTION ) {
            //$controller->toolbarMBZ = array("<a href='".Yii::app()->createUrl("/".$controller->module->id."/organization/dashboard/id/".$id)."'><i class='fa fa-group'></i>Organization</a>");
            $organization = Organization::getById($id);
            $nameParentTitle = $organization["name"];
            $controller->title = $organization["name"]."'s Rooms";
            $controller->subTitle = "Every Organization thinks, talks & decides.";
            $controller->pageTitle = "Communecter - ".$controller->title;
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
            $roomsActions = Person::getActionRoomsByPersonIdByType(Yii::app()->session['userId'],$type,$id);
         else 
            $rooms = ActionRoom::getWhereSortLimit( $where, array("date"=>1), 15);
        
        $rooms   = $roomsActions["rooms"];
        $actions = $roomsActions["actions"];

        error_log("count rooms : ".count($rooms));

        $params = array(    "rooms" => $rooms, 
                            "nameParentTitle" => $nameParentTitle, 
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