<?php
class IndexAction extends CAction
{
    public function run($type=null, $id= null)
    {
        $controller=$this->getController();
        
        $controller->title = "Timeline";
        $controller->subTitle = "NEWS comes from everywhere, and from anyone.";
        $controller->pageTitle = "Communecter - Timeline Globale";

        if(!function_exists("array_msort")){
			function array_msort($array, $cols)
			{
			    $colarr = array();
			    foreach ($cols as $col => $order) {
			        $colarr[$col] = array();
			        foreach ($array as $k => $row) { $colarr[$col]['_'.$k] = strtolower($row[$col]); }
			    }
			    $eval = 'array_multisort(';
			    foreach ($cols as $col => $order) {
			        $eval .= '$colarr[\''.$col.'\'],'.$order.',';
			    }
			    $eval = substr($eval,0,-1).');';
			    eval($eval);
			    $ret = array();
			    foreach ($colarr as $col => $arr) {
			        foreach ($arr as $k => $v) {
			            $k = substr($k,1);
			            if (!isset($ret[$k])) $ret[$k] = $array[$k];
			            $ret[$k][$col] = $array[$k][$col];
			        }
			    }
			    return $ret;
			
			}
		}
        //mongo search cmd : db.news.find({created:{'$exists':1}})	
		$params = array();
		if (!isset($id)){
			if(@$_GET["id"])
				$id=$_GET["id"];
			else 
				$id = Yii::app() -> session["userId"] ;
		}	
		if(!@$type)
			$type= Person::COLLECTION;
		$params["type"] = $type; 
        if( $type == Project::COLLECTION ) {
            $project = Project::getById($id);
            $onclick = "showAjaxPanel( '/project/detail/id/".$id."?isNotSV=1', 'PROJET DETAIL : ".$project["name"]."','lightbulb-o' )";
	        $entry = array('tooltip' => "Back to Project Details",
                            "iconClass"=>"fa fa-lightbulb-o",
                            "href"=>"<a  class='tooltips  btn btn-default' href='#' onclick=\"".$onclick."\"");
            //Menu::add2MBZ($entry);
            $params["project"] = $project; 
            $controller->title = $project["name"]."'s Timeline";
            $controller->subTitle = "Every Project is story to be told.";
            $controller->pageTitle = "Communecter - ".$controller->title;
        } 
        else if( $type == Person::COLLECTION ) {
            $person = Person::getById($id);
            $onclick = "showAjaxPanel( '/person/detail/id/".$id."?isNotSV=1', 'PERSON DETAIL : ".$person["name"]."','user' )";
	        $entry = array('tooltip' => "Back to Person Details",
                            "iconClass"=>"fa fa-user",
                            "href"=>"<a  class='tooltips  btn btn-default' href='#' onclick=\"".$onclick."\"");
            
            $params["person"] = $person; 
            $controller->title = $person["name"]."'s Timeline";
            $controller->subTitle = "Everyone has story to tell.";
            $controller->pageTitle = "Communecter - ".$controller->title;
        } 
        else if( $type == Organization::COLLECTION ) {
            $organization = Organization::getById($id);
            $onclick = "showAjaxPanel( '/organization/detail/id/".$id."?isNotSV=1', 'ORGANIZATION DETAIL : ".$organization["name"]."','group' )";
	        $entry = array('tooltip' => "Back to Organization Details",
                            "iconClass"=>"fa fa-group",
                            "href"=>"<a  class='tooltips  btn btn-default' href='#' onclick=\"".$onclick."\"");
			//Menu::add2MBZ($entry);
            $params["organization"] = $organization; 
            $controller->title = $organization["name"]."'s Timeline";
            $controller->subTitle = "Every Organization has story to tell.";
            $controller->pageTitle = "Communecter - ".$controller->title;
        }
        else if( $type == Event::COLLECTION ) {
            $event = Event::getById($id);
            $onclick = "showAjaxPanel( '/organization/detail/id/".$id."?isNotSV=1', 'EVENT DETAIL : ".$event["name"]."','calendar' )";
	        $entry = array('tooltip' => "Back to Event Details",
                            "iconClass"=>"fa fa-calendar",
                            "href"=>"<a  class='tooltips  btn btn-default' href='#' onclick=\"".$onclick."\"");
            $params["event"] = $event; 
            $controller->title = $event["name"]."'s Timeline";
            $controller->subTitle = "Every Event has story to tell.";
            $controller->pageTitle = "Communect - ".$controller->title;
        }


        $where = array("created"=>array('$exists'=>1),"text"=>array('$exists'=>1) ) ;
        // if(isset($type))
        // 	$where["type"] = $type;
        // if(isset($id))
        // 	$where["id"] = $id;
        if($type == "citoyen") $type = "citoyens";
        $where["scope.".$type] = $id;

        //TODO : get since a certain date
        $news = News::getWhereSortLimit( $where, array("date"=>1) ,30);

        //TODO : get all notifications for the current context
        
		if ( $type == Project::COLLECTION ){ 
			//GET NEW CONTRIBUTOR
			$paramContrib = array("verb" => ActStr::VERB_INVITE, "target.objectType" => $type, "target.id" => $id);
			$newsContributor=ActivityStream::getActivtyForObjectId($paramContrib);
			if(isset($newsContributor)){
				foreach ($newsContributor as $key => $data){
					$newsObject=NewsTranslator::convertToNews($data,NewsTranslator::NEWS_CONTRIBUTORS);
					$news[$key]=$newsObject;
				}
			}
			//GET NEW NEED
			$paramNeed = array("verb" => ActStr::VERB_CREATE, "object.objectType" => Need::COLLECTION, "target.objectType" => $type, "target.id" => $id);
			$newsNeed=ActivityStream::getActivtyForObjectId($paramNeed);
			if(isset($newsNeed)){
				foreach ($newsNeed as $key => $data){
					$newsObject=NewsTranslator::convertToNews($data,NewsTranslator::NEWS_CREATE_NEED);
					$news[$key]=$newsObject;
				}
			}
			// GET NEW EVENT
			$paramEvent = array("verb" => ActStr::VERB_CREATE, "object.objectType" => Event::COLLECTION,"target.objectType" => $type, "target.id" => $id);
			$newsEvent=ActivityStream::getActivtyForObjectId($paramEvent);
			if(isset($newsEvent)){
				foreach ($newsEvent as $key => $data){
					$newsObject=NewsTranslator::convertToNews($data,NewsTranslator::NEWS_CREATE_EVENT);
					$news[$key]=$newsObject;
				}
			}
			// GET NEW TASK
			$paramTask = array("verb" => ActStr::VERB_CREATE, "object.objectType" => Gantt::COLLECTION,"target.objectType" => $type, "target.id" => $id);
			$newsTask=ActivityStream::getActivtyForObjectId($paramTask);
			if(isset($newsTask)){
				foreach ($newsTask as $key => $data){
					$newsObject=NewsTranslator::convertToNews($data,NewsTranslator::NEWS_CREATE_TASK);
					$news[$key]=$newsObject;
				}
			}
		}
		if ( $type == Person::COLLECTION ){ 
			//GET NEW PROJECT
			$paramProject = array("verb" => ActStr::VERB_CREATE, "object.objectType" => Project::COLLECTION,"actor.objectType" => $type, "actor.id" => $id);
			$newsProject=ActivityStream::getActivtyForObjectId($paramProject);
			if(isset($newsProject)){
				foreach ($newsProject as $key => $data){
					$newsObject=NewsTranslator::convertToNews($data,NewsTranslator::NEWS_CREATE_PROJECT);
					$news[$key]=$newsObject;
				}
			}
			// GET EVENT FOR PERSON
			$paramEvent = array("verb" => ActStr::VERB_CREATE, "object.objectType" => Event::COLLECTION,"actor.objectType" => $type, "actor.id" => $id);
			$newsEvent=ActivityStream::getActivtyForObjectId($paramEvent);
			if(isset($newsEvent)){
				foreach ($newsEvent as $key => $data){
					$newsObject=NewsTranslator::convertToNews($data,NewsTranslator::NEWS_CREATE_EVENT);
					$news[$key]=$newsObject;
				}
			}
			// GET ORGANIZATION FOR PERSON
			$paramOrga = array("verb" => ActStr::VERB_CREATE, "object.objectType" => Organization::COLLECTION,"actor.id" => $id, "actor.objectType" => Person::COLLECTION);
			$newsOrga=ActivityStream::getActivtyForObjectId($paramOrga);
			if(isset($newsOrga)){
				foreach ($newsOrga as $key => $data){
					$newsObject=NewsTranslator::convertToNews($data,NewsTranslator::NEWS_CREATE_ORGANIZATION);
					$news[$key]=$newsObject;
				}
			}
		}
		if ( $type == Organization::COLLECTION ){ 
			//GET EVENT FOR ORGA
			$paramEvent = array("verb" => ActStr::VERB_CREATE, "object.objectType" => Event::COLLECTION,"target.objectType" => $type, "target.id" => $id);
			$newsEvent=ActivityStream::getActivtyForObjectId($paramEvent);
			if(isset($newsEvent)){
				foreach ($newsEvent as $key => $data){
					$newsObject=NewsTranslator::convertToNews($data,NewsTranslator::NEWS_CREATE_EVENT);
					$news[$key]=$newsObject;
				}
			}
			//GET PROJECT FOR ORGA
			$paramProject = array("verb" => ActStr::VERB_CREATE, "object.objectType" => Project::COLLECTION,"actor.objectType" => $type, "actor.id" => $id);
			$newsProject=ActivityStream::getActivtyForObjectId($paramProject);
			if(isset($newsProject)){
				foreach ($newsProject as $key => $data){
					$newsObject=NewsTranslator::convertToNews($data,NewsTranslator::NEWS_CREATE_PROJECT);
					$news[$key]=$newsObject;
				}
			}
			// GET NEW MEMBER FOR ORGANIZATION
			$paramMember = array("verb" => ActStr::VERB_JOIN, "target.objectType" => $type,"target.id" => $id);
			$newsMember=ActivityStream::getActivtyForObjectId($paramMember);
			print_r($newsMember);
			if(isset($newsMember)){
				foreach ($newsMember as $key => $data){
					$newsObject=NewsTranslator::convertToNews($data,NewsTranslator::NEWS_JOIN_ORGANIZATION);
					$news[$key]=$newsObject;
				}
			}

		}

		$news = array_msort($news, array('created'=>SORT_DESC));
        //TODO : reorganise by created date
		$params["news"] = $news; 
		$params["contextParentType"] = $type; 
		$params["contextParentId"] = $id;
		$params["userCP"] = Yii::app()->session['userCP'];
		$params["contextParentType"] = $type;
		$params["contextParentId"] = $id;
								//print_r($params["news"]);
		if(Yii::app()->request->isAjaxRequest)
	        echo $controller->renderPartial("index" , $params ,true);
	    else
  			$controller->render( "index" , $params  );
    }
}