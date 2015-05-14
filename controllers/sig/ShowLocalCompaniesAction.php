<?php
class ShowLocalCompaniesAction extends CAction
{
    public function run()
    {
        $whereGeo = $this->getGeoQuery($_POST, 'geo');
	    $where = array('type' => "company");
	    
	    $where = array_merge($where, $whereGeo);
	    				
    	$companies = PHDB::find(Organization::COLLECTION, $where);
    	$companies["origine"] = "ShowLocalCompanies";
    	  	
        Rest::json( $companies );
        Yii::app()->end();
    }
}