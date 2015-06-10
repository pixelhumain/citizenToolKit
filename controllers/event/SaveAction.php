<?php
class SaveAction extends CAction
{
    public function run()
    {
        $controller=$this->getController();

        $canSave = false;
        
        //TODO check by key
        if(!Event::checkExistingEvents($_POST))
        { 
        	if(isset($_POST["startDate"]) && isset($_POST["endDate"])){
        		if((strtotime($_POST["startDate"]) < strtotime($_POST["endDate"])) && (time() < strtotime($_POST["endDate"]))){
        			$canSave = true;
        		}else{
        			Rest::json(array("result"=>false, "msg"=>"Invalid end-Date"));
        		}
        	}
        	else{
        		Rest::json(array("result"=>false, "msg"=>"Invalid date"));
        	}
        	if(isset($_POST["allDay"]) && $_POST["allDay"] || $canSave){
	          	try {
	            	$res = Event::saveEvent($_POST);
	            } catch (CTKException $e) {
	            	$res = array("result"=>false, "msg"=>$e->getMessage());
	            }

          		Rest::json($res);
          	}
        } else
            Rest::json(array("result"=>false, "msg"=>"Cette Evenement existe déjà."));
       
        exit;
    }
}