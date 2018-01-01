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
				$where=array("orderedItemId"=>$_POST["id"],"orderedItemType"=>$_POST["type"]);
				if(@$_POST["start"]){
					$where["reservations"]=array(
						'$elemMatch'=> array(
							"date"=>array(
								'$gte'=> new MongoDate(strtotime($_POST["start"]))//,
        						//'$lte'=> new MongoDate(strtotime($_POST["end"]))
        					)
        				)
        			);
				}
				//Save the organization
				$orderItems=OrderItem::getListBy($where);
				$res=array("result"=>true, "msg"=>"list of items weel generated !", "items"=> $orderItems);
				Rest::json($res);
			} else {
				return Rest::json(array("result"=>false, "msg"=>"You are not loggued with a valid user !"));
			}
		} catch (CTKException $e) {
			return Rest::json(array("result"=>false, "msg"=>$e->getMessage()));
		}
    }
}