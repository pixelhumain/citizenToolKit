<?php
/**
* DateTimeAction send UTC time to the Smart-Citizen-Kit (User-Agent: SmartCitizen), and local time to other User-Agent.
*  
* @author: Jean Daniel CAZAL <danzalkay551@gmail.com>
* Date: 20/01/2017
* TODO use argument to select the utc or local 
*/

class DateTimeAction extends CAction {

    public function run() {
		
		$bindMap = null;
		$headers = getallheaders();
		if ($headers['User-Agent'] != 'SmartCitizen') {
			// Note : réponse incorrecte -> voir dans Thing::getDateTime -> dans Translate::convert
			$bindMap = TranslateCommunecter::$dataBinding_time;
			
			$resDateTime = Thing::getDateTime($bindMap);
			echo json_encode($resDateTime,true);
			//Rest::json($resDateTime);

		} else { 
			$resDateTime = gmdate("e:Y,n,j,H,i,s#"); 
			echo $resDateTime;
		}	
    }
}

?>