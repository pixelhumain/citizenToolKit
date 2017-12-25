<?php
class SearchAction extends CAction
{
    public function run($q=null,$tag=null,$type=null,$view=null)
    {

        if(!@$id && isset(Yii::app()->session["userId"])){
            $id = Yii::app()->session["userId"];
            $type = Person::COLLECTION;
        }
        $controller=$this->getController();

        
        $links = array();
        $tags = array();
        $strength = 0.085;
        $hasOrga = false;
        $hasKnows = false;
        $hasEvents = false;
        $hasProjects = false;
        $hasMembersO = false;
        $hasMembersP = false;

        $searchCrit = array();
        $crit = "";
        if(@$tag){
            $searchCrit["searchTag"]= array($tag);
            $crit = $tag;
        }
        else if(@$q){
            $searchCrit["name"]= $q;
            $crit = $q;
        }
        else if(@$type){
            $searchCrit["searchType"]= array($type);
            $crit = $type;
        }
        

        $root = array( "id" => "search", "group" => 0,  "label" => "search : ".$crit, "level" => 0 );
        $data = array($root);

        $list = Search::globalAutoComplete( $searchCrit );
        if(isset($list)){
        	foreach ($list as $key => $value){
                $types = array(Organization::COLLECTION, Organization::TYPE_BUSINESS , Organization::TYPE_NGO, Organization::TYPE_GROUP, Organization::TYPE_GOV, Project::COLLECTION, Event::COLLECTION, Person::COLLECTION);
                if(in_array($value['type'], $types)){    

                    if(  in_array($value['type'], array( Organization::COLLECTION,Organization::TYPE_BUSINESS , Organization::TYPE_NGO, Organization::TYPE_GROUP, Organization::TYPE_GOV))  ){
	        			if(!$hasOrga){
                            array_push($data, array( "id" => "orgas", "group" => 1,  "label" => "ORGANIZATIONS", "level" => 1 ) );
                            array_push($links, array( "target" => "orgas", "source" => "search",  "strength" => $strength ) );
                            $hasOrga = true;
                        }
                        array_push($data, array( "id" => (string)@$value["_id"], "group" => 2,  "label" => @$value["name"], "level" => 2,"type"=>Organization::COLLECTION,"tags" => @$value["tags"] ) );
                        array_push($links, array( "target" => (string)@$value["_id"], "source" => "orgas",  "strength" => $strength  ) );

	        		} else if ($value['type'] == Person::COLLECTION ){
                        if(!$hasKnows){
                            array_push($data, array( "id" => "people", "group" => 1,  "label" => "KNOWS", "level" => 1) );
                            array_push($links, array( "target" => "people", "source" => "search",  "strength" => $strength ) );
                            $hasKnows = true;
                        }
	        			array_push($data, array( "id" => (string)@$value["_id"], "group" => 1,  "label" => @$value["name"], "level" => 2,"tags" => @$value["tags"],"type"=>Person::COLLECTION ) );
                        array_push($links, array( "target" => (string)@$value["_id"], "source" => "people",  "strength" => $strength ,"tags" => @$value["tags"]) );
	        		} else if ($value['type'] == Event::COLLECTION ){
                        if(!$hasEvents){
                            array_push($data, array( "id" => "events", "group" => 1,  "label" => "EVENTS", "level" => 1 ) );
                            array_push($links, array( "target" => "events", "source" => "search",  "strength" => $strength ) );
                            $hasEvents = true;
                        }
	        			array_push($data, array( "id" => (string)@$value["_id"], "group" => 4,  "label" => @$value["name"], "level" => 2,"type"=>Event::COLLECTION,"tags" => @$value["tags"] ));
                        array_push($links, array( "target" => (string)@$value["_id"], "source" => "events",  "strength" => $strength ) );
	        		} else if ($value['type'] == Project::COLLECTION ){
	        			if(!$hasProjects){
                            array_push($data, array( "id" => "projects", "group" => 1,  "label" => "PROJECTS", "level" => 1 ) );
                            array_push($links, array( "target" => "projects", "source" => "search",  "strength" => $strength ) );
                            $hasProjects = true;
                        }
	        			array_push($data, array( "id" => (string)@$value["_id"], "group" => 3,  "label" => @$value["name"], "level" => 2,"type"=>Project::COLLECTION,"tags" => @$value["tags"] ));
                        array_push($links, array( "target" => (string)@$value["_id"], "source" => "projects",  "strength" => $strength ) );
	        		}
                    if(@$value["tags"]){
                        foreach (@$value["tags"] as $ix => $tag) 
                        {
                            if(!in_array($tag, $tags))
                                $tags[] = $tag;
                        }
                    }
        		}
        	}
        }

        $params = array( 
            'data' => $data, 
            'links' => $links,
            'tags' => $tags);

        if($view)
            Rest::json($data);
        else{
            if(Yii::app()->request->isAjaxRequest)
                $controller->renderPartial('d3', $params);
            else{
                Yii::app()->theme  = "empty";
                $controller->render('d3_2', $params);
            }
        }
    }
}
