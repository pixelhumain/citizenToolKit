<?php
class ViewerAction extends CAction
{
    public function run($id, $type,$data=null)
    {
        $controller=$this->getController();

        $itemType = Person::COLLECTION;
        if($type == "organization"){
        	$itemType = Organization::COLLECTION;
        }else if($type == "event"){
        	$itemType = Event::COLLECTION;
        }else if($type == "project"){
        	$itemType = Project::COLLECTION;
        }

        $item = PHDB::findOne( $itemType ,array("_id"=>new MongoId($id)));

        $viewerMap = array($type => $item);
        $viewerMap[Organization::COLLECTION] = array();
        $viewerMap[Event::COLLECTION] = array();
        $viewerMap[Person::COLLECTION] = array();
        $viewerMap[Project::COLLECTION] = array();
        if(isset($item) && isset($item["links"])){
        	foreach ($item["links"] as $key => $value){
        		foreach ($value as $k => $v) {

        			if(strcmp($key, "memberOf") == 0 || strcmp($key, "organizer") == 0){
	        			$obj = Organization::getById($k);
	        			array_push($viewerMap[Organization::COLLECTION], $obj);
	        		}else if (strcmp($key, "knows") == 0 || strcmp($key, "attendees") == 0 || strcmp($key, "contributors") == 0){
	        			$obj = Person::getById($k);
                $obj["type"] = "person";
	        			array_push($viewerMap[Person::COLLECTION], $obj);
	        		}else if (strcmp($key, "events") == 0){
	        			$obj = Event::getById($k);
	        			array_push($viewerMap[Event::COLLECTION], $obj);
	        		}else if (strcmp($key, "projects") == 0){
	        			$obj = Project::getById($k);
                $obj["type"] = "projects";
	        			array_push($viewerMap[Project::COLLECTION], $obj);
	        		}else if(strcmp($key, "members")== 0){
	        			if(isset($v["type"])){
		        			if(strcmp($v["type"], Organization::COLLECTION) == 0){
		        				$obj = Organization::getById($k);
		        				array_push($viewerMap[Organization::COLLECTION], $obj);
		        			}else if(strcmp($v["type"], Person::COLLECTION)== 0){
		        				$obj = Person::getById($k);
                    $obj["type"] = "person";
		        				array_push($viewerMap[Person::COLLECTION], $obj);
		        			}
		        		}
	        		}
        		}
        	}
        }

        $params = array('viewerMap' => $viewerMap);
        $params["typeMap"] = $type;

        if($data)
            Rest::json($viewerMap);
        else{
            if(Yii::app()->request->isAjaxRequest)
                $controller->renderPartial('viewer', $params);
            else{
                Yii::app()->theme  = "empty";
                $controller->render('viewer', $params);
            }
        }
    }
}
