<?php
/**
* GetThingByLocalityAction 
*  
* @author: Danzal
* Date: 28/04/2017
* 
*/
class GetThingByLocalityAction Action extends CAction {
    
    public function run($type ='smartCitizen', $country="RE", $regionName=null, $depName=null, $cityName=null, $cp=null, $insee=null){
    	//TOdo : prendre un array de types pour trouver tous les objets par localité en une seul requete. types=array('smartcitizen', 'copi')

    	$res = array();

    	if(preg_match('/smartCitizen/i',$type)==1){

    		$res['smartCitizen'] = Thing::getSCKDevicesByLocality($country, $regionName, $depName, $cityName, $cp, $insee);

    	}
    	if (preg_match('/copi/i',$type)==1) {
    		//$res['copi']=Thing::getCOPIByLocality();
    	}

    	Rest::json($res);
		Yii::app()->end(); 
    }

}