<?php
class RemoveHelpBlockAction extends CAction {
    
    public function run($id=null) {
		$controller=$this->getController();
		$res = Person::updateNotSeeHelpCo($id); 
		Rest::json($res);
    }
}