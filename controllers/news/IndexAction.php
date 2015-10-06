<?php
class IndexAction extends CAction
{
    public function run($type=null, $id= null)
    {
        $controller=$this->getController();
        
        $controller->title = "Timeline";
        $controller->subTitle = "NEWS comes from everywhere, and from anyone.";
        $controller->pageTitle = "Communecter - Timeline Globale";

        //mongo search cmd : db.news.find({created:{'$exists':1}})	

        if( $type == Project::COLLECTION ) {
            $controller->toolbarMBZ = array("<a href='".Yii::app()->createUrl("/".$controller->module->id."/project/dashboard/id/".$id)."'><i class='fa fa-lightbulb-o'></i>Project</a>");
            $project = Project::getById($id);
            $controller->title = $project["name"]."'s Timeline";
            $controller->subTitle = "Every Project is story to be told.";
            $controller->pageTitle = "Communecter - ".$controller->title;
        } else if( $type == Person::COLLECTION ) {
            $controller->toolbarMBZ = array("<a href='".Yii::app()->createUrl("/".$controller->module->id."/person/dashboard/id/".$id)."'><i class='fa fa-user'></i>Person</a>");
            $person = Person::getById($id);
            $controller->title = $person["name"]."'s Timeline";
            $controller->subTitle = "Everyone has story to tell.";
            $controller->pageTitle = "Communecter - ".$controller->title;
        } else if( $type == Organization::COLLECTION ) {
            $controller->toolbarMBZ = array("<a href='".Yii::app()->createUrl("/".$controller->module->id."/organization/dashboard/id/".$id)."'><i class='fa fa-group'></i>Organization</a>");
            $organization = Organization::getById($id);
            $controller->title = $organization["name"]."'s Timeline";
            $controller->subTitle = "Every Organization has story to tell.";
            $controller->pageTitle = "Communecter - ".$controller->title;
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
        $lastReadingDate
        $newsNotifications = ActivityStream::getActivityAsNews( $where );

        //TODO : reorganise by created date
        $news = array_merge($news,$newsNotifications);

		if(Yii::app()->request->isAjaxRequest)
	        echo $controller->renderPartial("index" , array( "news"=>$news, "userCP"=>Yii::app()->session['userCP'] ),true);
	    else
  			$controller->render( "index" , array( "news"=>$news, "userCP"=>Yii::app()->session['userCP'] ) );
    }
}