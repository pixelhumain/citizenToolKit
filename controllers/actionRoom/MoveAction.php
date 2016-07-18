<?php
class MoveAction extends CAction
{
    public function run()
    {
        error_log("saveSession");
        
        $type = $_POST['type'];
        $id = $_POST['id'];
        $destId = $_POST['destId'];
        
        //type is the type of the original element
        $element = null;
        if($type == ActionRoom::TYPE_SURVEY )
        {
        	$element = Survey::getById($id);
        	$room = ActionRoom::getById($survey["survey"]);
        	$collection = Survey::COLLECTION;
        } 
        else if($type == ActionRoom::TYPE_ACTION )
        {
        	$element = ActionRoom::getActionById($id);
        	$room = ActionRoom::getById($destId);
        	$collection = ActionRoom::TYPE_ACTIONS;
        }

        $res = array();
        if( $room && Yii::app()->session["userId"] && Authorisation::canParticipate( Yii::app()->session['userId'], $room["parentType"], $room["parentId"] )  )
        {
            
        	$destRoom = ActionRoom::getById($destId);
            if( $destRoom )
            {
            	$entryInfos = array();
	        	$res['destRoom'] = $destRoom;
                $res['result'] = true;

	        	if( isset($element) && in_array($destRoom['type'], array( ActionRoom::TYPE_ACTIONS ))){
	        		if($type == ActionRoom::TYPE_SURVEY)
	        		{
		        		//use case 2 : converting a survey to an action
			        	// - remove the survey attribute and add the room attribute
		        		unset($element["survey"]);
		        		$element["room"] = $destId;
		        		$element["type"] = ActionRoom::TYPE_ACTION;
		        		//delete source
	                	PHDB::remove( $collection,  array("_id" => new MongoId($_POST['id'])));
	                    // - copy from survey to actions collection
	                    $result = PHDB::insert( ActionRoom::TYPE_ACTIONS,$element );

		        		//return url is an action
		        		$res['url'] = "#rooms.actions.id.".$destId;
		        		$res['msg'] = Yii::t("rooms","Moved Succesfully to action room : ",null,Yii::app()->controller->module->id).$destRoom["name"];
	        		} else  {
		        		//use case 1 : move an action to a different action Room
		            	// switching the room attribute
			        	$entryInfos["room"] = $destId;
	                	$result = PHDB::update( $collection,  array("_id" => new MongoId($_POST['id'])), 
	                                               array('$set' => $entryInfos ));

	                	$res['url'] = "#survey.entries.id.".$destId;
	                	$res['msg'] = Yii::t("rooms","Moved Succesfully to ",null,Yii::app()->controller->module->id).$destRoom["name"];
		        	}
	        	} elseif ( isset($element) && in_array($destRoom['type'], array( ActionRoom::TYPE_VOTE ))){
	        		if($type == ActionRoom::TYPE_ACTION)
	        		{
		        		//use case 2 : converting an action to a survey
			        	// - remove the room attribute and add the survey attribute
		        		unset($element["room"]);
		        		$action["survey"] = $destId;
		        		$action["type"] = Survey::TYPE_ENTRY;
		        		//delete source
	                	PHDB::remove( $collection,  array("_id" => new MongoId($_POST['id'])));
	                    // - copy from survey to actions collection
	                    $result = PHDB::insert( Survey::COLLECTION,$action );

		        		//return url is an action
		        		$res['url'] = "#survey.entries.id.".$destId;
		        		$res['msg'] = Yii::t("rooms","Moved Succesfully to Decision room : ",null,Yii::app()->controller->module->id).$destRoom["name"];
		        	} else  {
		        		//use case 1 : move a proposal to a different survey 
		            	// switching the survey attribute
			        	$entryInfos["survey"] = $destId;
	                	$result = PHDB::update( $collection,  array("_id" => new MongoId($_POST['id'])), 
	                                               array('$set' => $entryInfos ));

	                	$res['url'] = "#survey.entries.id.".$destId;
	                	$res['msg'] = Yii::t("rooms","Moved Succesfully to ",null,Yii::app()->controller->module->id).$destRoom["name"];
		        	}
		        } else {
                	$res['result'] = false;
                	$res['msg'] = Yii::t("common","Something went wrong!");
		        }

                //Notify Element participants 
                //Notification::actionOnPerson ( ActStr::VERB_ADD_ACTION, ActStr::ICON_ADD, "", array( "type" => ActionRoom::COLLECTION_ACTIONS , "id" => $actionId ));
                
            } else
                $res = array('result' => false , 'msg'=>Yii::t("rooms","Destination Room doen't exist",null,Yii::app()->controller->module->id));
        } else
            $res = array('result' => false , 'msg'=>Yii::t("common","Something went wrong!"));
            
        Rest::json($res);  
        Yii::app()->end();
    }
}