<?php
class IndexAction extends CAction
{
    public function run($type=null, $id= null)
    {
        $controller=$this->getController();
        
        $controller->title = "Timeline";
        $controller->subTitle = "NEWS comes from everywhere, and from anyone.";
        $controller->pageTitle = "Communecter - Timeline Globale";
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
        //mongo search cmd : db.news.find({created:{'$exists':1}})	
		$params = array();
		$params["type"] = $type; 
        if( $type == Project::COLLECTION ) {
            $controller->toolbarMBZ = array("<a href='".Yii::app()->createUrl("/".$controller->module->id."/project/dashboard/id/".$id)."'><i class='fa fa-lightbulb-o'></i>Project</a>");
            $project = Project::getById($id);
            $params["project"] = $project; 
            $controller->title = $project["name"]."'s Timeline";
            $controller->subTitle = "Every Project is story to be told.";
            $controller->pageTitle = "Communecter - ".$controller->title;
        } else if( $type == Person::COLLECTION ) {
            $controller->toolbarMBZ = array("<a href='".Yii::app()->createUrl("/".$controller->module->id."/person/dashboard/id/".$id)."'><i class='fa fa-user'></i>Person</a>");
            $person = Person::getById($id);
            $params["person"] = $person; 
            $controller->title = $person["name"]."'s Timeline";
            $controller->subTitle = "Everyone has story to tell.";
            $controller->pageTitle = "Communecter - ".$controller->title;
        } else if( $type == Organization::COLLECTION ) {
            $controller->toolbarMBZ = array("<a href='".Yii::app()->createUrl("/".$controller->module->id."/organization/dashboard/id/".$id)."'><i class='fa fa-group'></i>Organization</a>");
            $organization = Organization::getById($id);
            $params["organization"] = $organization; 
            $controller->title = $organization["name"]."'s Timeline";
            $controller->subTitle = "Every Organization has story to tell.";
            $controller->pageTitle = "Communecter - ".$controller->title;
        }
        else if( $type == Event::COLLECTION ) {
            $controller->toolbarMBZ = array("<a href='".Yii::app()->createUrl("/".$controller->module->id."/event/dashboard/id/".$id)."'><i class='fa fa-group'></i>Event</a>");
            $event = Event::getById($id);
            $params["event"] = $event; 
            $controller->title = $event["name"]."'s Timeline";
            $controller->subTitle = "Every Event has story to tell.";
            $controller->pageTitle = "Communect - ".$controller->title;
        }


        $where = array("created"=>array('$exists'=>1),"text"=>array('$exists'=>1) ) ;
        if(isset($type))
        	$where["type"] = $type;
        if(isset($id))
        	$where["id"] = $id;
        //var_dump($where);
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
			$paramEvent = array("verb" => ActStr::VERB_CREATE, "object.objectType" => Event::COLLECTION,"target.objectType" => $type, "target.id" => $id);
			$newsEvent=ActivityStream::getActivtyForObjectId($paramEvent);
						print_r($newsEvent);
			if(isset($newsEvent)){
				foreach ($newsEvent as $key => $data){
					$newsObject=NewsTranslator::convertToNews($data,NewsTranslator::NEWS_CREATE_EVENT);
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
			$paramEvent = array("verb" => ActStr::VERB_CREATE, "object.objectType" => Event::COLLECTION,"actor.objectType" => $type, "actor.id" => $id);
			$newsEvent=ActivityStream::getActivtyForObjectId($paramEvent);
						print_r($newsEvent);
			if(isset($newsEvent)){
				foreach ($newsEvent as $key => $data){
					$newsObject=NewsTranslator::convertToNews($data,NewsTranslator::NEWS_CREATE_EVENT);
					$news[$key]=$newsObject;
				}
			}
		}
		if ( $type == Organization::COLLECTION ){ 
			//GET EVENT FOR ORGA
			$paramEvent = array("verb" => ActStr::VERB_CREATE, "object.objectType" => Event::COLLECTION,"target.objectType" => $type, "target.id" => $id);
			$newsEvent=ActivityStream::getActivtyForObjectId($paramEvent);
						print_r($newsEvent);
			if(isset($newsEvent)){
				foreach ($newsEvent as $key => $data){
					$newsObject=NewsTranslator::convertToNews($data,NewsTranslator::NEWS_CREATE_EVENT);
					$news[$key]=$newsObject;
				}
			}
		}
		$news = array_msort($news, array('created'=>SORT_DESC));
        //TODO : reorganise by created date

		$params["news"] = $news; 
		$params["userCP"] = Yii::app()->session['userCP'];
		if(Yii::app()->request->isAjaxRequest)
	        echo $controller->renderPartial("index" , $params ,true);
	    else
  			$controller->render( "index" , $params  );
    }
}