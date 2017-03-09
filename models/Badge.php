<?php

class Badge {


	public static function getBagdes($idItem, $typeItem) {
		$badges = array();
		$account = PHDB::findOneById($typeItem ,$idItem, array("badges"));
		if(!empty($account["badges"]))
			$badges = $account["badges"];
		return $badges;
	}

	public static function checkBadgeInListBadges($badge, $badges) {
		
		$res = false ;
		foreach ($badges as $key => $value) {
			if($badge == $value["name"]){
				$res = true ;
				break;
			}
		}
		return $res;
	}

	public static function addBadgeInListBadges($badge, $badges) {
		$res = array(	"result" => false, 
						"badges" => $badges, 
						"msg" => Yii::t("import","Le badge est déjà dans la liste"));
		if(is_array($badge)){
			$newListBadges = array();
			foreach ($badge as $key => $value) {
				if(!self::checkBadgeInListBadges((empty($value["name"])?$value:$value["name"]), $badges)){
					$newBadge["name"] = (empty($value["name"])?$value:$value["name"]);
					$newBadge["date"] = (empty($value["date"])?new mongoDate(time()):$value["date"]);
					$newListBadges[] = $newBadge;
				}
			}
			$badges = array_merge($badges, $newListBadges);
			$res = array("result" => true, "badges" => $badges);

		}else if(is_string($badge)){
			if(!self::checkBadgeInListBadges($badge, $badges)){
				$newBadge["name"] = $badge;
				$newBadge["date"] = new mongoDate(time());
				$badges[] = $newBadge;
				$res = array("result" => true, "badges" => $badges);
			}
		}
		return $res;
	}

	public static function updateBadges($badges, $idItem, $typeItem) {
		$res = array("result" => false, "msg" => Yii::t("import","La mise à jour a échoué."));
		if($typeItem == Person::COLLECTION)
			$res = Person::updatePersonField($idItem, "badges", $badges, Yii::app()->session["userId"]);
		else if($typeItem == Organization::COLLECTION)
			$res = Organization::updateOrganizationField($idItem, "badges", $badges, Yii::app()->session["userId"]);
		else if($typeItem == Event::COLLECTION)
			$res = Event::updateEventField($idItem, "badges", $badges, Yii::app()->session["userId"]);
		else if($typeItem == Project::COLLECTION)
			$res = Project::updateProjectField($idItem, "badges", $badges, Yii::app()->session["userId"]);

		return $res;
	}

	public static function addAndUpdateBadges($nameBadge, $idItem, $typeItem) {
		$badges = self::getBagdes($idItem, $typeItem);
		if(empty($badges))
           $badges = array();

       	$resAddBadge = self::addBadgeInListBadges($nameBadge, $badges);
		if($resAddBadge["result"] == true){
			$res = self::updateBadges($resAddBadge["badges"], $idItem, $typeItem);
		}else
			$res = array("result" => false, "msg" => $resAddBadge["msg"]);

		return $res;
	}


	public static function conformeBadges($badges) {
		$newListBadges = array();
		if(is_array($badges)){
			foreach ($badges as $key => $value) {
				$newBadge["name"] = (empty($value["name"])?$value:$value["name"]);
				$newBadge["date"] = (empty($value["date"])?new mongoDate(time()):$value["date"]);
				$newListBadges[] = $newBadge;
			}
		}else if(is_string($badges)){
			$newBadge["name"] = $badges;
			$newBadge["date"] = new mongoDate(time());
			$newListBadges[] = $newBadge;
		}
		return $newListBadges;
	}


	public static function delete($nameBadge, $idItem, $typeItem) {
		$badges = self::getBagdes($idItem, $typeItem);
		$newBadges = array();
		foreach ($badges as $key => $badge) {
			if($badge["name"] != $nameBadge)
				$newBadges[] = $badge;
		}
		$res = self::updateBadges($newBadges, $idItem, $typeItem);
		return $res ;
	}

}

?>