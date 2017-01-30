<?php
class ShowLocalStateAction extends CAction
{
    public function run()
    {
        $whereGeo = $this->getGeoQuery($_POST, 'geo');
	    $where = array('type' => "association");
	    
	    
	    $where = array_merge($where, $whereGeo);
	    				
    	$states = PHDB::find(Organization::COLLECTION, $where);
    	$states["origine"] = "ShowLocalState";
    	   	
        Rest::json( $states );
        Yii::app()->end();
    }
}