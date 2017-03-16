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
		$element = Element::getByTypeAndId($type,$id);
		$links=array();
		$links = (!empty($element["links"])?Element::getAllLinks($element["links"],$type,$id):array());
        /*if(@$_POST["links"]){
            $links = $_POST["links"];
        }else{
	        $links=array();
            if(@$element["links"]){
                $links = Element::getAllLinks($element["links"],$type,$id);
            }
        }*/
		$show=true;
        if($type==Person::COLLECTION)
            $show = Preference::showPreference($element, $type, "directory", Yii::app()->session["userId"]);
        
        $contextIcon = "connectdevelop";
        $contextTitle = "";
        $contextIconTitle = "connectdevelop";
        if( @$type == Organization::COLLECTION){
            $contextTitle = Yii::t("common","Community of organization");
            $connectType="members";
            $contextIconTitle = "group text-green";
        }
        else if(@$type == Event::COLLECTION){
            $contextTitle = Yii::t("common", "Community of event");
            $parentType=Event::COLLECTION;
            $contextIconTitle = "circle text-orange";
            $connectType="attendees";
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

        $countPeople = 0; $countOrga = 0; $countProject = 0; $countEvent = 0; $countFollowers = 0; $countAttendees = 0; $countGuests = 0; $followsPeople = 0; $followsOrga = 0; $followsProject = 0;
        if($show == true){
            if (@$links["people"])
                foreach ($links["people"] as $key => $onePeople) { if( @$onePeople["name"]) $countPeople++;}
            if (@$links["organizations"])
                foreach ($links["organizations"] as $key => $orga) { if( @$orga["name"]&& ($type != Organization::COLLECTION || $id!=$orga["id"])) $countOrga++;  }
            if (@$links["projects"])
                foreach ($links["projects"] as $key => $project) { if( @$project["name"] && ($type != Project::COLLECTION || $id!=$project["id"])) $countProject++;  }
            if (@$links["events"])
                foreach ($links["events"] as $key => $event) { if( @$event["name"] && ($type != Event::COLLECTION || $id!=$event["id"])) $countEvent++;  }
            if (@$links["followers"])
                foreach ($links["followers"] as $key => $follower) { if( @$follower["name"]) $countFollowers++;}
            if (@$links["attendees"])
                foreach ($links["attendees"] as $key => $attendee) { if( @$attendee["name"]) $countAttendees++;}
            if (@$links["guests"])
                foreach (@$links["guests"] as $key => $guest) { if( @$guest["name"]) $countGuests++;}
            if (@$links["follows"]){
				if(@$links["follows"][Person::COLLECTION]){ 
					foreach ($links["follows"][Person::COLLECTION] as $e) {
						$followsPeople++;
						$countPeople++;
					}
				}
				if(@$links["follows"][Organization::COLLECTION]){ 
					foreach ($links["follows"][Organization::COLLECTION] as $e) {
						$followsOrga++;
						$countOrga++;
					}
				}
				if(@$links["follows"][Project::COLLECTION]){ 
					foreach ($links["follows"][Project::COLLECTION] as $e) {
						$followsProject++;
						$countProject++;
					}
				}
			
			}
        }
        
        // Est-ce vraiment utiliser
        $followsProject = 0; $followsPeople = 0 ; $followsOrga = 0;

        $params = array(
            "organizations" => (($show == true)?@$links["organizations"]:null),
            "events" => (($show == true)?@$links["events"]:null),
            "people" => (($show == true)?@$links["people"]:null),
            "projects" => (($show == true)?@$links["projects"]:null),
            "followers" => (($show == true)?@$links["followers"]:null),
            "attendees" => (($show == true)?@$links["attendees"]:null),
            "guests" => (($show == true)?@$links["guests"]:null),
            "follows" => (($show == true)?@$links["follows"]:null),
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
        $params["show"] = $show;
        $params["type"] = $type;
        $params["elementId"] = $id;
        $params["element"] = $element;
        $params["connectType"] = @$connectType;
        $params["links"] = $links;
        $params["contextIcon"] = $contextIcon ;
        $params["contextTitle"] = $contextTitle ;
        $params["contextIconTitle"] = $contextIconTitle ;
        //$params["manage"] = ( @$connectType && @$element["links"][$connectType][Yii::app()->session["userId"]] )?1:0;
        $params["edit"] = Authorisation::canEditItem(@Yii::app()->session["userId"], $type, $id);
        $params["manage"] = $params["edit"];
       // if($type == Person::COLLECTION && @Yii::app()->session["userId"] && $id==Yii::app()->session["userId"])
       	//	$params["manage"] = 1;        	

        $params["openEdition"] = Authorisation::isOpenEdition($id, $type, @$element["preferences"]);
        $page = "directory2";
        if(Yii::app()->request->isAjaxRequest){
            if(@$_GET[ "tpl" ] == "json"){
              $context = array("name"=>$element["name"]);
              //controllons le resultat
              $list = array(
                "citoyens" => $params["people"],
                "organizations" => $params["organizations"],
                "events" => $params["events"],
                "projects" => $params["projects"],
                );
              echo Rest::json( array( "list" => $list,"context"=>$context) );
            }
            else
            echo $controller->renderPartial($page,$params,true);
        }
        else {
            $controller->render($page,$params);
        }
    }
}
