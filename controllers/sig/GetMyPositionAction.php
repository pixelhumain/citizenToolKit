<?php
class GetMyPositionAction extends CAction
{
    public function run()
    {
        $where = array(	'_id'  => new MongoId(Yii::app()->session["userId"]) );
	 	$user = PHDB::find(Person::COLLECTION, $where);
    	 
    	 //si l'utilisateur connecté n'a pas enregistré sa position geo
    	 //on prend la position de son CP
    	 foreach($user as $me)
    	 if(!isset($me["geo"]) && isset($me["cp"])) {
    		$res = array(Yii::app()->session["userId"] => 
						SIG::getGeoPositionByCp($me["cp"]));
			Rest::json( $res );
			Yii::app()->end();
        }
		Rest::json( $user );
		Yii::app()->end();
    }
}