<?php
class DeleteAction extends CAction
{
    public function run($eventId)
    {
    	$res = Link::removeEventLinks($eventId);
    	if($res["ok"]==1){
    		Event::delete($eventId);
    	}
    	
    	Rest::json(array('result' => true, "msg" => "Evenement bien supprimé"));
    }
}