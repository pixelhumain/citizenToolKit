<?php

class Role {

	/**
	 * Default Roles for a new person : 
	 *  - tobeactivated : true
	 *  - betaTester : false
	 *  - superAdmin : false
	 *  - standalonePageAccess : true
	 * @return array of role
	 */
	public static function getDefaultRoles()	{
		//Manage roles of user
	  	$roles = array();
	  	$roles["tobeactivated"] = true;
	  	//By default no one is beta tester in a betaTest mode
	  	$roles["betaTester"] = false;
	  	//Can access standalone Pages : true
	  	$roles["standalonePageAccess"] = true;
	  	//Not a super admin
	  	$roles["superAdmin"] = false;

	  	return $roles;  	
	}

	private static function checkUserRoles($person) {
		if (! @$person["roles"]) {
			//Maybe it's an old user : we add the default role
			//And retrieve the tobevalidated indicator
			$roles = self::getDefaultRoles();
			$roles["tobeactivated"] = @$person["tobeactivated"];
			Person::updatePersonField((String) $person["_id"], "roles", $roles, (String)$person["_id"]);
		} else {
			$roles = $person["roles"];
		}
		return $roles;
	}

	public static function canUserLogin($person) {
		$res = array("result"=>true, 
                    "msg"=>"Everything is ok : user can login !");

		$roles = self::checkUserRoles($person);
		//The account is not validated
        if (isset($roles["tobeactivated"]) && @$roles["tobeactivated"] ) {
            return array("result"=>false, 
              "msg"=>"Your account is not validated : please check your mailbox to validate your user");
        }
        
        //BetaTest mode
        if (@Yii::app()->params['betaTest']) {
        	if (isset($roles["betaTester"]) && ! @$roles["betaTester"]) {
				$res = array("result"=>false, 
                    "msg"=>"We're still finishing things, see you in september");
			}
        }
        
        //TODO - manage standalone page access
        return $res;
	}

	public static function isUserSuperAdmin($roles) {
		if (! $roles) {
			throw new CTKException("The user does not have roles set on his profile : contact your admin");
		}

		if ($roles["superAdmin"]) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Update the roles' list of an organization
	 * @param $roleTab is an array with all the roles
	 * @param type $organisationId : is the mongoId of the organisation
	 */
	//TODO - is it still used ?
	public static function setRoles($roleTab, $itemId, $itemType){
		PHDB::update( $itemType,
						array("_id" => new MongoId($itemId)), 
                        array('$set' => array( 'roles' => $roleTab))
                    );
	}
}
?>