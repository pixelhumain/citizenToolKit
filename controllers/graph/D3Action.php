<?php
class D3Action extends CAction
{
    public function run($id=null, $type=null,$view=null)
    {
        if(!@$id && isset(Yii::app()->session["userId"])){
            $id = Yii::app()->session["userId"];
            $type = Person::COLLECTION;
        }
        $controller=$this->getController();

        $itemType = Person::COLLECTION;
        if($type == "organization"){
        	$itemType = Organization::COLLECTION;
        }else if($type == "event"){
        	$itemType = Event::COLLECTION;
        }else if($type == "project"){
        	$itemType = Project::COLLECTION;
        }

        $item = PHDB::findOne( $itemType , array("_id"=>new MongoId($id)) );
        $root = array( "id" => (string)$item["_id"], "group" => 0,  "label" => $item["name"], "level" => 0,"tags" => @$item["tags"] );

        $data = array($root);
        $links = array();
        $tags = array();
        $strength = 0.095;
        $hasOrga = false;
        $hasKnows = false;
        $hasEvents = false;
        $hasProjects = false;
        $hasMembersO = false;
        $hasMembersP = false;
        $hasTags = false;

        $link = "#page.type.".$itemType.".id.".(string)$item["_id"];

        if(@$item["tags"])
            foreach (@$item["tags"] as $ix => $tag) {if(!in_array($tag, $tags))$tags[] = $tag;}
        
        
        if(isset($item) && isset($item["links"])){
        	foreach ($item["links"] as $key => $value){
        		foreach ($value as $k => $v) {
                    
                    if(strcmp($key, "memberOf") == 0 || strcmp($key, "organizer") == 0){
	        			$obj = Organization::getById($k);
                        if(!@$obj["_id"] || !@$obj["name"])continue;
                        if(!$hasOrga){
                            array_push($data, array( "id" => "orgas", "group" => 1,  "label" => "ORGANIZATIONS", "level" => 1 ) );
                            array_push($links, array( "target" => "orgas", "source" => $root["id"],  "strength" => $strength ) );
                            $hasOrga = true;
                        }
                        array_push($data, array( "id" => (string)@$obj["_id"], "group" => 2,  "label" => @$obj["name"], "level" => 2,"type"=>Organization::CONTROLLER,"tags" => @$obj["tags"], "linkSize" => count(@$obj["links"], COUNT_RECURSIVE),"img"=>@$obj["profilThumbImageUrl"] ) );
                        array_push($links, array( "target" => (string)@$obj["_id"], "source" => "orgas",  "strength" => $strength  ) );

	        		}

                    else if (strcmp($key, "knows") == 0  || 
                              strcmp($key, "attendees") == 0 || 
                              strcmp($key, "contributors") == 0 ){
	        			$obj = Person::getById($k);
                        if(!@$obj["_id"] || !@$obj["name"])continue;
                        $obj["type"] = "person";
                        if(!$hasKnows){
                            array_push($data, array( "id" => "knows", "group" => 1,  "label" => "KNOWS", "level" => 1) );
                            array_push($links, array( "target" => "knows", "source" => $root["id"],  "strength" => $strength ) );
                            $hasKnows = true;
                        }
	        			array_push($data, array( "id" => (string)@$obj["_id"], "group" => 1,  "label" => @$obj["name"], "level" => 2,"tags" => @$obj["tags"],"type"=>Person::COLLECTION, "linkSize" => count(@$obj["links"], COUNT_RECURSIVE),"img"=>@$obj["profilThumbImageUrl"] ) );
                        array_push($links, array( "target" => (string)@$obj["_id"], "source" => "knows",  "strength" => $strength ,"tags" => @$obj["tags"]) );
	        		}

                    else if (strcmp($key, "events") == 0){
	        			$obj = Event::getById($k);
                        if(!@$obj["_id"] || !@$obj["name"])continue;
                        if(!$hasEvents){
                            array_push($data, array( "id" => "events", "group" => 1,  "label" => "EVENTS", "level" => 1 ) );
                            array_push($links, array( "target" => "events", "source" => $root["id"],  "strength" => $strength ) );
                            $hasEvents = true;
                        }
	        			array_push($data, array( "id" => (string)@$obj["_id"], "group" => 4,  "label" => @$obj["name"], "level" => 2,"type"=>Event::CONTROLLER,"tags" => @$obj["tags"], "linkSize" => count(@$obj["links"], COUNT_RECURSIVE),"img"=>@$obj["profilThumbImageUrl"] ));
                        array_push($links, array( "target" => (string)@$obj["_id"], "source" => "events",  "strength" => $strength ) );
	        		}

                    else if (strcmp($key, "projects") == 0){
	        			$obj = Project::getById($k);
                        if(!@$obj["_id"] || !@$obj["name"])continue;
                        $obj["type"] = "projects";
                        if(!$hasProjects){
                            array_push($data, array( "id" => "projects", "group" => 1,  "label" => "PROJECTS", "level" => 1 ) );
                            array_push($links, array( "target" => "projects", "source" => $root["id"],  "strength" => $strength ) );
                            $hasProjects = true;
                        }
	        			array_push($data, array( "id" => (string)@$obj["_id"], "group" => 3,  "label" => @$obj["name"], "level" => 2,"type"=>Project::CONTROLLER,"tags" => @$obj["tags"], "linkSize" => count(@$obj["links"], COUNT_RECURSIVE),"img"=>@$obj["profilThumbImageUrl"] ));
                        array_push($links, array( "target" => (string)@$obj["_id"], "source" => "projects",  "strength" => $strength ) );
	        		}

                    else if(strcmp($key, "members")== 0){
	        			if(isset($v["type"])){
		        			if(strcmp($v["type"], Organization::COLLECTION) == 0){
		        				$obj = Organization::getById($k);
                                if(!@$obj["_id"] || !@$obj["name"])continue;
                                if(!$hasMembersO){
                                    array_push($data, array( "id" => "memberso", "group" => 1,  "label" => "MEMBERS org", "level" => 1 ) );
                                    array_push($links, array( "target" => "memberso", "source" => $root["id"],  "strength" => $strength ) );
                                    $hasMembersO = true;
                                }
		        				array_push($data, array( "id" => (string)@$obj["_id"], "group" => 2,  "label" => @$obj["name"], "level" => 2,"type"=>Organization::COLLECTION,"tags" => @$obj["tags"], "linkSize" => count(@$obj["links"], COUNT_RECURSIVE),"img"=>@$obj["profilThumbImageUrl"] ));
                                array_push($links, array( "target" => (string)@$obj["_id"], "source" => "memberso",  "strength" => $strength ) );

		        			}

                            else if(strcmp($v["type"], Person::COLLECTION)== 0){
		        				$obj = Person::getById($k);
                                if(!@$obj["_id"] || !@$obj["name"])continue;
                                $obj["type"] = "person";
                                if(!$hasMembersP){
                                    array_push($data, array( "id" => "membersp", "group" => 1,  "label" => "MEMBERS people", "level" => 1 ) );
                                    array_push($links, array( "target" => "membersp", "source" => $root["id"],  "strength" => $strength ) );
                                    $hasMembersP = true;
                                }
		        				array_push($data, array( "id" => (string)@$obj["_id"], "group" => 1,  "label" => @$obj["name"], "level" => 2,"type"=>Person::COLLECTION,"tags" => @$obj["tags"], "linkSize" => count(@$obj["links"], COUNT_RECURSIVE) ));
                                array_push($links, array( "target" => (string)@$obj["_id"], "source" => "membersp",  "strength" => $strength ) );
		        			}
		        		}
	        		}


                    if(@$obj["tags"]){
                        foreach (@$obj["tags"] as $ix => $tag) {
                            if(!in_array($tag, $tags)){
                                $tags[] = $tag;
                                if(!$hasTags){
                                    array_push($data, array( "id" => "tags", "group" => 1,  "label" => "TAGS", "level" => 1 ) );
                                    array_push($links, array( "target" => "tags", "source" => $root["id"],  "strength" => $strength ) );
                                    $hasTags = true;
                                }
                                array_push($data, array( "id" => "tag".(count($tags)), "group" => 1,  "label" => $tag, "level" => 2,"type"=>"tag" ));
                                array_push($links, array( "target" => "tag".(count($tags)), "source" => "tags",  "strength" => $strength ) );
                            }
                        }
                    }
        		}
        	}
        }

        $params = array( 
            'data' => $data, 
            'links' => $links,
            'item' => $item,
            'tags' => $tags,
            "typeMap" => $type,
            "colink" => "#page.type.".$itemType.".id.".(string)$item["_id"],
            "title" => $type." : ".$item["name"],
            "link" => $link
            );

        Yii::app()->theme  = "empty";
        Yii::app()->session["theme"] = "empty";

        if($view)
            Rest::json($data);
        else{
            if(Yii::app()->request->isAjaxRequest)
                $controller->renderPartial('d3', $params);
            else{
                Yii::app()->theme  = "empty";
                $controller->render('d3', $params);
            }
        }
    }
}
