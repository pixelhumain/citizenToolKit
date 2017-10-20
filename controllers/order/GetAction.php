<?php

class GetAction extends CAction
{
    /**
	* Save a new organization with the minimal information
	* @return an array with result and message json encoded
	*/
    public function run($id=null) {
		$controller=$this->getController();
		// Retrieve data from form
		//$newOrganization = Order::newOrganizationFromPost($_POST);
		try{
			if ( Person::logguedAndValid() ) {
				//Save the organization
				$list=Order::getOrderItemById($id);
				$res=array("result"=>true, "msg"=>"list of items weel generated !", "list"=> array("orderItems"=>$list));
				Rest::json($res);
			} else {
				return Rest::json(array("result"=>false, "msg"=>"You are not loggued with a valid user !"));
			}
		} catch (CTKException $e) {
			return Rest::json(array("result"=>false, "msg"=>$e->getMessage()));
		}
    }
}