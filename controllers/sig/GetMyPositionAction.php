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
            //error_log("MODULE type : " . $type);
            if(@$me['profilImageUrl'] && $me['profilImageUrl'] != ""){
                $profilMarkerImageUrl = "/". $type . "/" . Yii::app()->session["userId"] . "/thumb/profil-marker.png";
                $profilMarkerExists = true;
            }else{
                $doc = new Document();
				$homeUrlRegEx = "/".str_replace("/", "\/", Yii::app()->homeUrl)."/";
				$assetsUrl = preg_replace($homeUrlRegEx, "", @Yii::app()->controller->module->assetsUrl,1);
                $profilMarkerImageUrl = "/".$assetsUrl."/images/sig/markers/icons_carto/citizen-marker-default.png";
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
                $geo = isset($me["geo"]) ? $me["geo"] : array("latitude" => 0, "longitude" => 0, "default" => true);
                $res = array("position" => $geo, 
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