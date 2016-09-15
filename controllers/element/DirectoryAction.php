<?php 
 /**
  * Display the dashboard of the person
  * @param String $id Not mandatory : if specify, look for the person with this Id. 
  * Else will get the id of the person logged
  * @return type
  */
class DirectoryAction extends CAction
{
    public function run( $type=null, $id=null )
    {
        $controller = $this->getController();
        // Vérifier si utile cette condition
		if(@$_POST["element"]){
			$element = $_POST["element"];
			if(@$_POST["links"])
				$links = $_POST["links"];
		}else{
			$element = Element::getByTypeAndId($type,$id);
			if(@$element["links"])
				$links = Element::getAllLinks($element["links"],$type);
		}
        //$params["organization"] = $organization;
        //$params["type"] = Organization::CONTROLLER;
        //$params["parentType"] = $type;
    	//$page = "element/directory";
        //if( isset($_GET[ "tpl" ]) )

        /*if($type == Organization::COLLECTION)
           $connectAs="members";
        else if($type == Project::COLLECTION)
            $connectAs="contributors";
        else if($type == Event::COLLECTION)
            $connectAs="attendees";*/

        $contextIcon = "connectdevelop";
        $contextTitle = "";
        $contextIconTitle = "connectdevelop";
        if( @$type == Organization::COLLECTION){
            $contextTitle = Yii::t("common","Community of organization");
            $connectType="members";
            $contextIconTitle = "group text-green";
        }
        else if(@$type == Event::COLLECTION){
            $contextTitle = Yii::t("common", "Visualize Event Communauty");
            $parentType=Event::COLLECTION;
            $contextIconTitle = "circle text-orange";
        }
        else if( @$type == Person::COLLECTION){
            $contextTitle =  Yii::t("common", "DIRECTORY of")." ".$element["name"];
            $connectType="network";
            $contextIconTitle = "user text-yellow";
        }
        else if( @$type == PROJECT::COLLECTION){
            $contextTitle = Yii::t("common", "Community of project");
            $connectType="contributors";
            $contextIconTitle = "circle text-purple";
        }



        $countPeople = 0; $countOrga = 0; $countProject = 0; $countEvent = 0; $countFollowers = 0; $countAttendees = 0; $countGuests = 0;
        if (@$people)
            foreach ($people as $key => $onePeople) { if( @$onePeople["name"]) $countPeople++;}
        if (@$organizations)
            foreach ($organizations as $key => $orga) { if( @$orga["name"]) $countOrga++;  }
        if (@$projects)
            foreach ($projects as $key => $project) { if( @$project["name"]) $countProject++;  }
        if (@$events)
            foreach ($events as $key => $event) { if( @$event["name"]) $countEvent++;  }
        if (@$followers)
            foreach ($followers as $key => $follower) { if( @$follower["name"]) $countFollowers++;}
        if (@$attendees)
            foreach ($attendees as $key => $attendee) { if( @$attendee["name"]) $countAttendees++;}
        if (@$guests)
            foreach ($guests as $key => $guest) { if( @$guest["name"]) $countGuests++;}
        // Est-ce vraiment utiliser
        $followsProject = 0; $followsPeople = 0 ; $followsOrga = 0;

        $params = array(
            "organizations" => @$links["organizations"],
            "events" => @$links["events"],
            "people" => @$links["people"],
            "projects" => @$links["projects"],
            "followers" => @$links["followers"],
            "countPeople" => @$countPeople,
            "countOrga" => @$countOrga,
            "countProject" => @$countProject,
            "countEvent" => @$countEvent,
            "countFollowers" => @$countFollowers,
            "countAttendees" => @$countAttendees,
            "countGuests" => @$countGuests,
            "followsProject" => @$followsProject,
            "followsPeople" => @$followsPeople,
            "followsOrga" => @$followsOrga
        ); 
        $params["type"] = $type;
        $params["elementId"] = $id;
        $params["element"] = $element;
        $params["contextIcon"] = $contextIcon ;
        $params["contextTitle"] = $contextTitle ;
        $params["contextIconTitle"] = $contextIconTitle ;
        $params["manage"] = ( @$connectType && @$element["links"][$connectType][Yii::app()->session["userId"]])?1:0;
        $params["edit"] = Authorisation::canEditItem(Yii::app()->session["userId"], $type, $element["_id"]);
        $params["openEdition"] = Authorisation::isOpenEdition($element["_id"], $type, @$element["preferences"]);







        $page = "directory2";
        if(Yii::app()->request->isAjaxRequest){
            echo $controller->renderPartial($page,$params,true);
        }
        else {
            $controller->render($page,$params);
        }
    }
}
