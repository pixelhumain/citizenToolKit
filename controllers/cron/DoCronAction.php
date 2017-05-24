<?php
class DoCronAction extends CAction {
	
	//Process the cron inside the cron collection
	//TODO SBAR - Add security with a token or an id of super admin ?
	public function run() {
		Cron::processCron();
	}
}