<?php
class IndexAction extends CAction
{
    public function run( $type=null, $id= null, $isAdmin=null)
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
            $parentName=$project["name"];
        } 
        else if( $type == Organization::COLLECTION ) {
            $controller->toolbarMBZ = array("<a href='".Yii::app()->createUrl("/".$controller->module->id."/organization/dashboard/id/".$id)."'><i class='fa fa-users'></i>Organization</a>");
            $organization = Organization::getById($id);
            $controller->title = $organization["name"]."'s Needs";
            $controller->subTitle = "Need's name // Every Project has a lack of ressources";
            $controller->pageTitle = "Communecter - ".$controller->title;
            $parentName=$organization["name"];
        } 
        array_push( $controller->toolbarMBZ, '<a href="#" class="newRoom" title="proposer une " ><i class="fa fa-plus"></i> Room </a>');
        $where = array("created"=>array('$exists'=>1) ) ;
        if(isset($type))
        	$where["parentType"] = $type;
        if(isset($id))
        	$where["parentId"] = $id;
        //var_dump($where);
		$needs = Need::getWhereSortLimit( $where, array("date"=>1) ,30);
		$params = array( "needs" => $needs, "isAdmin"=> $isAdmin, "parentName" => $parentName);
		if(Yii::app()->request->isAjaxRequest)
	        echo $controller->renderPartial("index",$params, true);
	    else
  			$controller->render( "index" , $params  );
    }
}