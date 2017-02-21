<?php
class ShowLocalEventsAction extends CAction
{
    public function run()
    {
        $whereGeo = array();//$this->getGeoQuery($_POST);
	    $where = array('geo' => array( '$exists' => true ));
	    
	    $where = array_merge($where, $whereGeo);
	    				
    	$events = PHDB::find(PHType::TYPE_EVENTS, $where);
    	
    	foreach($events as $event)
    	{
    	//dans le cas où un événement n'a pas de position geo, 
    	//on lui ajoute grace au CP
    	//il sera visible sur la carte au prochain rechargement
    		if(!isset($event["geo"]))
    		{
				$cp = $event["location"]["address"]["addressLocality"];
				$queryCity = array( "cp" => strval(intval($cp)),
									"geo" => array('$exists' => true) ); 
				$city =  Yii::app()->mongodb->cities->findOne($queryCity); //->limit(1)
				if($city!=null)
				{ 
					$newPos = array('geo' => array("@type" => "GeoCoordinates",	
										"longitude" => floatval($city['geo']['coordinates'][0]),
										"latitude"  => floatval($city['geo']['coordinates'][1]) ),
									'geoPosition' => $city['geo']
							  );
					Yii::app()->mongodb->events->update( array("_id" => $event["_id"]), 
                	                                   	 array('$set' => $newPos ) );
        		}
    		}
    	}
    	$events["origine"] = "ShowLocalEvents";
    	Rest::json( $events );
        Yii::app()->end();
    }
}