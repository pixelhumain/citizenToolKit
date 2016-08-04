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
		$links=@$_POST["links"];
		$params = array(
            "organizations" => @$links["organizations"],
            "events" => @$links["events"],
            "people" => @$links["people"],
            "projects" => @$links["projects"],
            "followers" => @$links["followers"]
        );  
       // $params["organization"] = $organization;
        $params["type"] = Organization::CONTROLLER;
        $params["parentType"] = $type;
        $params["parentId"] = $id;
    	$page = "../element/directory";
        if( isset($_GET[ "tpl" ]) )
          $page = "../element/directory2";
        if(Yii::app()->request->isAjaxRequest){
            echo $controller->renderPartial($page,$params,true);
        }
        else {
            $controller->render($page,$params);
        }
    }
}
