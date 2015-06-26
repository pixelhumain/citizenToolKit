<?php
class IndexAction extends CAction
{
    public function run($type=null, $id= null)
    {
        $controller=$this->getController();
    
        //mongo search cmd : db.news.find({created:{'$exists':1}})	

        if( $type == Project::COLLECTION )
        {
            $controller->toolbarMBZ = array(
                    "<a href='".Yii::app()->createUrl("/".$controller->module->id."/project/dashboard/id/".$id)."'><i class='fa fa-lightbulb-o'></i>Project</a>",
                    "<a href='".Yii::app()->createUrl("/".$controller->module->id."/news/index/type/projects/id/".$id)."'><i class='fa fa-rss fa-2x'></i>TIMELINE</a>"
            );
            $project = Project::getById($id);
            $controller->title = $project["name"]."'s Exchange Place";
            $controller->subTitle = "Exchange about subject";
            $controller->pageTitle = "Communecter - Espace de discussion";
        }

		if(Yii::app()->request->isAjaxRequest)
	        echo $controller->renderPartial("index" , array(), true);
	    else
  			$controller->render( "index" , array() );
    }
}