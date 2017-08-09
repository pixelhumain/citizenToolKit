<?php
/**
* DateTimeAction send UTC time to the Smart-Citizen-Kit, 
* and local time to other User-Agent.
*  
* @author: Jean Daniel CAZAL
* Date: 20/01/2017
* Modified : 29/05/2017
* TODO use argument to select the utc or local for other User-Agent
*/

class DateTimeAction extends CAction {
	public function run() {
		$headers = getallheaders();
		if ($headers['User-Agent'] != 'SmartCitizen') {
			Rest::json(
				Thing::getDateTime(
					TranslateCommunecter::$dataBinding_time));
		} else { 
			echo gmdate("e:Y,n,j,H,i,s#"); 
		}	
	}
}
?>