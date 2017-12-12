<?php

class Role {

	const ADD_BETA_TESTER = "addBetaTester";
	const REVOKE_BETA_TESTER = "revokeBetaTester";
	const ADD_SUPER_ADMIN = "addSuperAdmin";
	const REVOKE_SUPER_ADMIN = "revokeSuperAdmin";
	const ADD_BANNED_USER = "addBannedUser";
	const REVOKE_BANNED_USER = "revokeBannedUser";
	const DEVELOPER = "developer";
	const SUPERADMIN = "superAdmin";
	const SOURCEADMIN = "sourceAdmin";
	const COEDITOR = "coEditor";

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
        //The account is not validated.
        if (@$roles["isBanned"]) {
            return array("result"=>false, "id" => @$person[_id], "msg"=>Yii::t("common","Your account has been certified as fraudulent towards the policies of respect"));
        }
        
        return $res;
	}

	public static function isUserSuperAdmin($roles) {
		if (! $roles) {
			return false;
			//throw new CTKException("The user does not have roles set on his profile : contact your admin");
		}
		//var_dump($roles);
		return (@$roles["superAdmin"]) ? true : false;
	}

	public static function isUserBetaTester($roles) {
		return (@Yii::app()->params['betaTest'] && @$roles["betaTester"]) ? true : false;
	}

	public static function isUser($userRoles, $roles) {
		$res =  array_intersect ( $userRoles , $roles );
		return ( count($res)>0 ) ? true : false;
	}

	public static function isSuperAdmin($roles) {
		return (@$roles[self::SUPERADMIN]) ? true : false;
	}

	public static function isDeveloper($roles) {
		return (@$roles[self::DEVELOPER]) ? true : false;
	}

	public static function isSourceAdmin($roles) {
		return (@$roles[self::SOURCEADMIN]) ? true : false;
	}

	public static function isUserActivated($id){
		$personRole = PHDB::findOneById( Person::COLLECTION ,$id, array("roles" => 1));
		return (!@$personRole["roles"]["tobeactivated"]) ? true : false;
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
		if ($action == self::REVOKE_BETA_TESTER || $action == self::REVOKE_SUPER_ADMIN || $action == self::REVOKE_BANNED_USER) {
			$mongoAction = '$unset';
			$roleValue = "";
		}

		if ($action == self::ADD_BETA_TESTER || $action == self::REVOKE_BETA_TESTER) {
			$role = 'betaTester';
		} else if ($action == self::ADD_SUPER_ADMIN || $action == self::REVOKE_SUPER_ADMIN) {
			$role = 'superAdmin';
		} else if ($action == self::ADD_BANNED_USER || $action == self::REVOKE_BANNED_USER) {
			$role = 'isBanned';
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