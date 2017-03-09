<?php

class Role {

	const ADD_BETA_TESTER = "addBetaTester";
	const REVOKE_BETA_TESTER = "revokeBetaTester";
	const ADD_SUPER_ADMIN = "addSuperAdmin";
	const REVOKE_SUPER_ADMIN = "revokeSuperAdmin";
	const DEVELOPER = "developer";
	const SUPERADMIN = "superAdmin";
	const SOURCEADMIN = "sourceAdmin";
	
	/**
	 * Default Roles for a new person : 
	 *  - tobeactivated : true
	 *  - betaTester : false
	 *  - superAdmin : false
	 *  - standalonePageAccess : true
	 * @return array of role
	 */
	public static function getDefaultRoles() {
		//Manage roles of user
	  	$roles = array();
	  	$roles["tobeactivated"] = true;
	  	//By default no one is beta tester in a betaTest mode
	  	if (@Yii::app()->params['betaTest'])
		  	$roles["betaTester"] = false;
	  	
	  	//Can access standalone Pages : true
	  	$roles["standalonePageAccess"] = true;
	  	
	  	//Not a super admin => No flag
	  	//$roles["superAdmin"] = false;

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

	public static function canUserLogin($person, $isRegisterProcess=false) {
		$res = array("result"=>true, 
                    "msg"=>"Everything is ok : user can login !");

		//check if the user has been created with minimal data
		if (@$person["pending"]) {
			return array("result"=>false, "pendingUserId" => (String) $person["_id"], "pendingUserEmail" => $person["email"], "msg"=>"accountPending");
		}

		$roles = self::checkUserRoles($person);
		
        if (@Yii::app()->params['betaTest']) {
        	if (isset($roles["betaTester"])) {
        		if (! $roles["betaTester"]) {
					return array("result"=>false, 
						"msg" => "betaTestNotOpen");
				}
			}
    	}    

	    //The account is not validated.
        if (isset($roles["tobeactivated"]) && @$roles["tobeactivated"]) {
            return array("result"=>false, "id" => @$person[_id], "msg"=>"notValidatedEmail");
        }
        
        return $res;
	}

	public static function isUserSuperAdmin($roles) {
		if (! $roles) {
			throw new CTKException("The user does not have roles set on his profile : contact your admin");
		}
		//var_dump($roles);
		if (@$roles["superAdmin"]) {
			return true;
		} else {
			return false;
		}
	}

	public static function isUserBetaTester($roles) {
		if (@Yii::app()->params['betaTest'] && @$roles["betaTester"]) {
			return true;
		} else {
			return false;
		}
	}

	public static function isSuperAdmin($roles) {
		if (@$roles[self::SUPERADMIN]) {
			return true;
		} else {
			return false;
		}
	}

	public static function isDeveloper($roles) {
		if (@$roles[self::DEVELOPER]) {
			return true;
		} else {
			return false;
		}
	}

	public static function isSourceAdmin($roles) {
		if (@$roles[self::SOURCEADMIN]) {
			return true;
		} else {
			return false;
		}
	}

	public static function isUserActivated($id){
		$personRole = PHDB::findOneById( Person::COLLECTION ,$id, array("roles" => 1));
		if (!@$personRole["roles"]["tobeactivated"])
			return true;
		else 
			return false;
	}
	
	/**
	 * Update the role list of an item
	 * @param String $action an action to make on a user role map. See type of RoleAction
	 * @param String $userId a valid user id
	 * @return array of result (result, msg)
	 */
	public static function updatePersonRole($action, $userId){
		$mongoAction = '$set';
		$roleValue = true;
		if ($action == self::REVOKE_BETA_TESTER || $action == self::REVOKE_SUPER_ADMIN) {
			$mongoAction = '$unset';
			$roleValue = "";
		}

		if ($action == self::ADD_BETA_TESTER || $action == self::REVOKE_BETA_TESTER) {
			$role = 'betaTester';
		} else if ($action == self::ADD_SUPER_ADMIN || self::REVOKE_SUPER_ADMIN) {
			$role = 'superAdmin';
		}

		PHDB::update( Person::COLLECTION,
						array("_id" => new MongoId($userId)), 
                        array($mongoAction => array( 'roles.'.$role => $roleValue))
                    );

		return array("result" => true, "msg" => "The role has been updated");
	}

	/**
	 * Update the roles' list of an organization
	 * @param $roleTab is an array with all the roles
	 * @param type $organisationId : is the mongoId of the organisation
	 */
	//TODO SBAR - is it still used ?
	public static function setRoles($roleTab, $itemId, $itemType){
		PHDB::update( $itemType,
						array("_id" => new MongoId($itemId)), 
                        array('$set' => array( 'roles' => $roleTab))
                    );
	}



	public static function getRolesUserId($id){
		$personRole = PHDB::findOneById( Person::COLLECTION ,$id, array("roles" => 1));
		if (!empty($personRole["roles"]))
			return $personRole["roles"];
		else 
			return false;
	}
}
?>