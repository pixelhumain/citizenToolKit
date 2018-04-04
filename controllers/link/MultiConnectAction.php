<?php

class MultiConnectAction extends CAction
{
	public function run() {
		$controller=$this->getController();
		if(!empty($_POST["parentId"]) && !empty($_POST["parentType"]) && !empty($_POST["listInvite"])) {
			try {



				$list = $_POST["listInvite"] ;
				$res = array();

				// var_dump(count($list["citoyens"]));var_dump(count($list["invites"]));var_dump(count($list["organizations"])); exit ;

				if( !empty($list["citoyens"]) && count($list["citoyens"]) > 0 ){

					foreach ($list["citoyens"] as $key => $value) {
						$child = array( "childId" => $key,
										"childType" => Person::COLLECTION);

						$child = array( "childId" => $_POST["parentId"],
										"childType" => $_POST["parentType"]);
						
						$res[] = Link::follow($key, Person::COLLECTION, $child);
					}
				}

				if( !empty($list["invites"]) && count($list["invites"]) > 0 ){

					foreach ($list["invites"] as $key => $value) {
						

						// $invitedUserId = Person::getPersonIdByEmail($key);

						// $child = array( "childId" => $_POST["parentId"],
						// 				"childType" => $_POST["parentType"]);
						
						// $res[] = Link::follow($invitedUserId, Person::COLLECTION, $child);

						$newPerson = array(	"name" => $value["name"],
											"email" => $key,
											"invitedBy" => Yii::app()->session["userId"]);

						$creatUser = Person::createAndInvite($newPerson, @$value["msg"]);

						if ($creatUser["result"]) {
							$invitedUserId = $creatUser["id"];
							$child["childId"] = $_POST["parentId"];
							$child["childType"] = $_POST["parentType"];
							$res[] = Link::follow($invitedUserId, Person::COLLECTION, $child);
						}
					}
				}

				if( !empty($list["organizations"]) && count($list["organizations"]) > 0 ){

					foreach ($list["organizations"] as $key => $value) {
						
						// Link::multiconnect($child, $parentId, $parentType)
						// $invitedUserId = Person::getPersonIdByEmail($key);

						//$child = array( "id" => $key,
						//				"type" => Person::COLLECTION);
						//Link::follow($_POST["parentId"], $_POST["parentType"], $child);
					}
				}

				//exit;
				return Rest::json($res);
				// $res = Element::updateBlock($_POST);
				// return Rest::json($res);
			} catch (CTKException $e) {
				return Rest::json(array("result"=>false, "msg"=>$e->getMessage(), "data"=>$_POST));
			}
		}
		return Rest::json(array("result"=>false,"msg"=>Yii::t("common","Invalid request")));
	}

}