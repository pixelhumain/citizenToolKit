<?php 
class Cooperation {

	/*
		* GET ALL ROOM FOR ONE ELEMENT  	:	getCoopData/contextType/contextId/room/
		* GET ONE ROOM 						:	getCoopData/contextType/contextId/room/all/roomId 

		* GET ALL PROPOSAL FOR ONE ELEMENT  :	getCoopData/contextType/contextId/proposal/
		* GET ONE PROPOSAL 					:	getCoopData/contextType/contextId/proposal/all/proposalId 
	*/

	public static function getCoopData($parentType, $parentId, $type, $status=null, $dataId=null){
		
		$res = array();

		if($type == Room::CONTROLLER){
			if(empty($dataId)){ //si pas d'id : prend toutes les rooms pour un element parent
				$status = empty($status) ? "open" : $status;
				$query = array( "parentType" => $parentType, "parentId" => $parentId, "status" => $status);
				$res["roomList"] = PHDB::findAndSort ( 
								Room::COLLECTION, $query, array("name" => 1));
			}else{ //si un d'id : prend récupère toutes les proposals & actions & resolutions de la room
				$res["room"] = Room::getById($dataId);

				$query = array( "idParentRoom" => $dataId );
				$res["proposalList"] = PHDB::findAndSort (Proposal::COLLECTION, $query, array("created" => -1));
				$res["actionList"] = PHDB::findAndSort (Action::COLLECTION, $query, array("created" => -1));

				/*$query = array( "idParentRoom" => $dataId );
				$res["proposalList"] = PHDB::findAndSort (Proposal::COLLECTION, $query, array("created" => -1));*/
			}
		}

		else if($type == Proposal::CONTROLLER){
			if(empty($dataId)){ //si pas d'id : prend toutes les rooms pour un element parent
				$query = array( "parentType" => $parentType, "parentId" => $parentId);
				if(!empty($status)) $query["status"] = $status;
				$res["proposalList"] = PHDB::findAndSort ( 
								  		Proposal::COLLECTION, $query, array("created" => -1));
			}else{ //si un d'id : prend récupère toutes les proposals & actions & resolutions de la room
				$res["proposal"] = Proposal::getById($dataId);
			}
		}

		else if($type == Action::CONTROLLER){
			if(empty($dataId)){ //si pas d'id : prend toutes les rooms pour un element parent
				$query = array( "parentType" => $parentType, "parentId" => $parentId);
				if(!empty($status)) $query["status"] = $status;
				$res["actionList"] = PHDB::findAndSort ( 
								  		Action::COLLECTION, $query, array("created" => -1));
			}else{ //si un d'id : prend récupère toutes les proposals & actions & resolutions de la room
				$res["action"] = Action::getById($dataId);
			}

		//}else if($type == Resolution::CONTROLLER){

		}
		
		$res["post"]["type"] = $type;
		$res["post"]["status"] = $status;

		return $res;
	}
}
