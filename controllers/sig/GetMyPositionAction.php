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

            $type = isset($me["type"]) ? $me["type"] : "citoyen";
                
        	if(!isset($me["geo"]) && isset($me["cp"])) {
                $res = array("position" => SIG::getPositionByCp($me["cp"]), 
                             "type" => $type, 
                             "typeSig" => PHType::TYPE_CITOYEN);
    			Rest::json( $res );
    			Yii::app()->end();
            }
            else{
                $res = array("position" => $me["geo"], 
                             "type" => $type, 
                             "typeSig" => PHType::TYPE_CITOYEN);
                Rest::json( $res );
                Yii::app()->end();
            }
        }
		
    }
}