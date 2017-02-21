<?php
class ShowMyNetworkAction extends CAction
{
    public function run()
    {
        $whereGeo = SIG::getGeoQuery($_POST, 'geo');
	    $where = array();//'cp' => array( '$exists' => true ));
	    
	    $where = array_merge($where, $whereGeo);
	    				
    	$citoyens = PHDB::find(Person::COLLECTION, $where);
    	$citoyens["origine"] = "ShowMyNetwork";
    	
    	
        Rest::json( $citoyens );
        Yii::app()->end();
    }
}