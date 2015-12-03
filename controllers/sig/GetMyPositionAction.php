<?php
class GetMyPositionAction extends CAction
{
    public function run()
    {
        $where = array(	'_id'  => new MongoId(Yii::app()->session["userId"]) );
	 	$user = PHDB::find(Person::COLLECTION, $where);
    	 
    	//si l'utilisateur connecté n'a pas enregistré sa position geo
    	//on prend la position de son CP
    	foreach($user as $me){
            $type = (isset($me["type"]) && $me["type"]!="") ? $me["type"] : Person::COLLECTION;
            error_log("MODULE type : " . $type);
            if(isset(Yii::app()->session['user']['profilImageUrl']) && Yii::app()->session['user']['profilImageUrl'] != ""){
                $profilMarkerImageUrl = "/". $type . "/" . Yii::app()->session["userId"] . "/thumb/profil-marker.png";
                $profilMarkerExists = true;
            }else{
                $doc = new Document();
                $markerDefaultName = $doc->getEmptyMarkerFileName($type);
                $profilMarkerImageUrl = "/".$assetsUrl."/images/sig/markers/icons_carto/".$markerDefaultName;
                $profilMarkerExists = false;
            }

            $type = isset($me["type"]) ? $me["type"] : "citoyen";
        	if(!isset($me["geo"]) && isset($me["cp"])) {
                $res = array("position" => SIG::getPositionByCp($me["cp"]), 
                             "type" => $type, 
                             "profilMarkerImageUrl" => $profilMarkerImageUrl,
                             "typeSig" => Person::COLLECTION);
    			Rest::json( $res );
    			Yii::app()->end();
            }
            else{
                $res = array("position" => $me["geo"], 
                             "type" => $type, 
                             "profilMarkerImageUrl" => $profilMarkerImageUrl,
                             "profilMarkerExists" => $profilMarkerExists,
                             "typeSig" => Person::COLLECTION);
                Rest::json( $res );
                Yii::app()->end();
            }
        }
		
    }
}