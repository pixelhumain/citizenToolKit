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
		if(@$_POST["element"]){
			$element = $_POST["element"];
			$links = $_POST["links"];
		}else{
			$element = Element::getByTypeAndId($type,$id);
			$links = Element::getAllLinks($element["links"],$type);
		}
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
        $params["element"] = $element;
    	//$page = "element/directory";
        //if( isset($_GET[ "tpl" ]) )

        if($type == Organization::COLLECTION)
           $connectAs="members";
        else if($type == Project::COLLECTION)
            $connectAs="contributors";
        else if($type == Event::COLLECTION)
            $connectAs="attendees";

        $params["manage"] = ( @$connectAs && @$element["links"][$connectAs][Yii::app()->session["userId"]])?1:0;
        
        $page = "directory2";
        if(Yii::app()->request->isAjaxRequest){
            echo $controller->renderPartial($page,$params,true);
        }
        else {
            $controller->render($page,$params);
        }
    }
}
