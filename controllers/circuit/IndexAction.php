<?php

class IndexAction extends CAction
{
    /**
	* Save a new organization with the minimal information
	* @return an array with result and message json encoded
	*/
    public function run($id=null) {
		$controller=$this->getController();
		// Retrieve data from form
		//$newOrganization = Order::newOrganizationFromPost($_POST);
		/*try{
			if ( Person::logguedAndValid() ) {
				//Save the organization
				Rest::json(Circuit::insert($_POST, Yii::app()->session["userId"]));
			} else {
				return Rest::json(array("result"=>false, "msg"=>"You are not loggued with a valid user !"));
			}
		} catch (CTKException $e) {
			return Rest::json(array("result"=>false, "msg"=>$e->getMessage()));
		}*/
		$viewRender=false;
		$manage="buy";
		if(@$_POST["object"])
			$object=$_POST["object"];
		else
			$object=Circuit::getById($id);
		if(@$_POST["viewRender"])
			$viewRender=$_POST["viewRender"];
		if(@$_POST["manage"])
			$manage=$_POST["manage"];
		$params=array("object"=>$object,"viewRender"=>$viewRender,"manage"=>$manage);
		if(@$_POST["backup"])
			$params["backup"]=$_POST["backup"];
		if(Yii::app()->request->isAjaxRequest)
			echo $controller->renderPartial("/pod/circuit",$params,true);
		else 
			$controller->render("/pod/circuit",$params);
    }
}