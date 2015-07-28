<?php
class DashboardAction extends CAction
{
    public function run( $idNeed=null, $type=null, $id= null)
    {
        $controller=$this->getController();
        
        $controller->title = "Action Needs";
        $controller->subTitle = "Define need in order to receive help from community";
        $controller->pageTitle = "Communecter - Action Needs";
        
        if( $type == Project::COLLECTION ) {
            $controller->toolbarMBZ = array("<a href='".Yii::app()->createUrl("/".$controller->module->id."/project/dashboard/id/".$id)."'><i class='fa fa-lightbulb-o'></i>Project</a>");
            $project = Project::getById($id);
            $controller->title = $project["name"]."'s Needs";
            $controller->subTitle = "Need's name // Every Project has a lack of ressources";
            $controller->pageTitle = "Communecter - ".$controller->title;
        } 
        array_push( $controller->toolbarMBZ, '<a href="#" class="newNeed" title="proposer une " ><i class="fa fa-plus"></i> Need </a>');
		//if(Yii::app()->request->isAjaxRequest)
	    //    echo $controller->renderPartial("index",true);
	    //else
  			$controller->render( "dashboard");
    }
}