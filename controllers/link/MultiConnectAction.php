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
						$child = array();
						if($_POST["parentType"] == Person::COLLECTION){
							// $child = array( "childId" => $key,
							// 			"childType" => Person::COLLECTION);

							$child = array( "childId" => $_POST["parentId"],
											"childType" => $_POST["parentType"]);
							$res["citoyens"][] = Link::follow($key, Person::COLLECTION, $child);
						}else if($_POST["parentType"] == Action::COLLECTION){

							$params = array( "id" => $_POST["parentId"],
											"child" => $key);
							$res["citoyens"][] = ActionRoom::assignPeople($params);
						} else {
							$child = array();
							$child[] = array( 	"childId" => $key,
												"childType" => Person::COLLECTION,
												"childName" => $value["name"],
												"roles"=> (empty($value["roles"]) ? array() : $value["roles"]),
												"connectType" => (empty($value["isAdmin"]) ? "" : $value["isAdmin"]) );
							//var_dump($child);
							
							$res["citoyens"][] = Link::multiconnect($child, $_POST["parentId"], $_POST["parentType"]);
						}
					}
				}

				if( !empty($list["invites"]) && count($list["invites"]) > 0 ){
					
					foreach ($list["invites"] as $key => $value) {
						$child = array();
						$newPerson = array(	"name" => $value["name"],
											"email" => $value["mail"],
											"invitedBy" => Yii::app()->session["userId"]);

						$creatUser = Person::createAndInvite($newPerson, @$value["msg"]);
						if ($creatUser["result"]) {
							if($_POST["parentType"] == Person::COLLECTION){
								$invitedUserId = $creatUser["id"];
								$child["childId"] = $_POST["parentId"];
								$child["childType"] = $_POST["parentType"];
								$res["invites"][] = Link::follow($invitedUserId, Person::COLLECTION, $child);
								
							} else if($_POST["parentType"] == Action::COLLECTION){

								$params = array( "id" => $_POST["parentId"],
												"child" => $creatUser["id"]);
								$res["citoyens"][] = ActionRoom::assignPeople($params);
								
							} else {
								$child = array();
								$child[] = array( 	"childId" => $creatUser["id"],
													"childType" => Person::COLLECTION,
													"childName" => $value["name"],
													"roles" => (empty($value["roles"]) ? array() : $value["roles"]),
													"connectType" => (empty($value["isAdmin"]) ? "" : $value["isAdmin"]) );
								//var_dump($child);
								$res["invites"][]= Link::multiconnect($child, $_POST["parentId"], $_POST["parentType"]);
							}
						}
					}
				}

				if( !empty($list["organizations"]) && count($list["organizations"]) > 0 ){
					
					foreach ($list["organizations"] as $key => $value) {
						$child = array();
						if($_POST["parentType"] == Person::COLLECTION){
							$child = array( "id" => $key,
											"type" => Person::COLLECTION);
							$res["citoyens"][] = Link::follow($_POST["parentId"], $_POST["parentType"], $child);
						}else{
							$child = array();
							$child[] = array( 	"childId" => $key,
												"childType" => Organization::COLLECTION,
												"childName" => $value["name"],
												"roles" => (empty($value["roles"]) ? array() : $value["roles"]) );
							
							$res["organizations"][] = Link::multiconnect($child, $_POST["parentId"], $_POST["parentType"]);
						}
					}
				}

				//var_dump($res); exit;
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