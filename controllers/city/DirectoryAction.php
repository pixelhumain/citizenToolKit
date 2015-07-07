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
        
  		$where = array("address.codeInsee"=>$insee);
  		$params["events"] = Event::getWhere( $where );
  		$params["organizations"] = Organization::getWhere($where);
  		$params["people"] = Person::getWhere($where);
  		$controller->render( "directory", $params );
    }
}
