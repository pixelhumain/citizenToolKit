<?php 
 /**
  * Display the dashboard of the person
  * @param String $id Not mandatory : if specify, look for the person with this Id. 
  * Else will get the id of the person logged
  * @return type
  */
class DirectoryAction extends CAction
{
    public function run( $insee=null )
    {
      $controller=$this->getController();


      $city = PHDB::findOne( City::COLLECTION , array( "insee" => $insee ) );
      $name = (isset($city["name"])) ? $city["name"] : "";
      $name2 = (isset($city["alternateName"])) ? $city["alternateName"] : "";
      $name .= ", ".$name2; 
      $controller->title = ( (!empty($name)) ? $name : "City : ".$insee)."'s Directory";
      $controller->subTitle = (isset($city["description"])) ? $city["description"] : "";
      $controller->pageTitle = ucfirst($controller->module->id)." - ".$controller->title;
        
  		$where = array("address.codeInsee"=>$insee);
  		$params["events"] = Event::getWhere( $where );
  		$params["organizations"] = Organization::getWhere( $where );
  		$params["people"] = Person::getWhere( $where );
      $params["projects"] = Project::getWhere( $where );
      $params["type"] = City::CONTROLLER;
      $params["city"] = $city;

      $page = "../default/directory";
        if( isset($_GET[ "tpl" ]) )
          $page = "../default/".$_GET[ "tpl" ];
        if(Yii::app()->request->isAjaxRequest){
            echo $controller->renderPartial($page,$params,true);
        } else {
            $controller->render($page,$params);
        }
    }
}
