<?php
class SaveAttendeesAction extends CAction
{
    public function run($idEvent = null, $attendeeId = null)
    {
        $res = array( "result" => false , "msg" => Yii::t("common","Something went wrong!") ,"event" => $idEvent);
       // if(Yii::app()->request->isAjaxRequest && isset( $idEvent))
        //{
        if( !$idEvent && isset($_POST["idEvent"]))
          $idEvent = $_POST["idEvent"];
        if( !$attendeeId && isset($_POST["attendeeId"]))
          $attendeeId = $_POST["attendeeId"];

        $event = (isset($idEvent)) ? PHDB::findOne(Event::COLLECTION,array("_id"=>new MongoId($idEvent))) : null;
        
        if($event)
        {	
	        $attendeeType = Person::COLLECTION;
	        if( isset($attendeeId) && !empty($attendeeId) )
            {
		        if( isset($event['links']["events"]) && isset( $event['links']["events"][$attendeeId] ))
                  $res = array( "result" => false , "msg" => Yii::t("event","Allready attending for this event",null,Yii::app()->controller->module->id) );
                else 
                {
                	Link::connect( $attendeeId, $attendeeType, $idEvent, Event::COLLECTION, Yii::app()->session["userId"], "events" );
					Link::connect( $idEvent, Event::COLLECTION, $attendeeId, $attendeeType, Yii::app()->session["userId"], "attendees" );
					$citoyen = Person::getPublicData( $attendeeId );

	            	if (!empty($citoyen)) 
                    {
		            	$profil = Document::getLastImageByKey($attendeeId, Person::COLLECTION, Document::IMG_PROFIL);
						if($profil !="")
							$citoyen["imagePath"]= $profil;
		            }
					$res = array("result"=>true, 
                                //"attendee" => $citoyen, 
                                "msg" => Yii::t("event","ATTENDEE SUCCESSFULLY ADD!!",null,Yii::app()->controller->module->id),
                                //"personLink"=>$personLink,
                                //"eventLink"=>$eventLink,
                                "reload"=>true);
                }
	        }
	        else
            {
            	$member = array(
  					'name'=>$_POST['name'],
  					'email'=>$_POST['email'],
  					'invitedBy'=>Yii::app()->session["userId"],
  					'created' => time(),
  					'links'=>array( 'events' => array($idEvent => array(
                                "type" => 'events',
                                //"tobeconfirmed" => true,
                                "invitedBy" => Yii::app()->session["userId"],
  							)
						)
					)	
				);
					
                $member = Person::createAndInvite($member);
               // print_r($newAttendee);             
                Link::connect($idEvent, Event::COLLECTION, $member["id"], $attendeeType, Yii::app()->session["userId"], "attendees" );
                //Link::connect($newAttendee["id"], $attendeeType, $idEvent, Event::COLLECTION, Yii::app()->session["userId"], "events" );
                $res = array("result"=>true,"attendee" => $member, "msg"=> Yii::t("event","Attendee well registered and invite!!",null,Yii::app()->controller->module->id) ,"reload"=>true);  
        	}
	        //Guide of attendee
	        // Add me as attendee (Btn at the top)
	        // Invite someone (People)
	        // To develop - private event or restricted on confirmation
			
            /*$memberEmail = $_POST['email'];

            if($_POST['type'] == "persons"){
              $memberType = Person::COLLECTION;
            }else{
              $memberType = Organization::COLLECTION;
            }
            if(isset($_POST["id"]) && $_POST["id"] != ""){
              $memberEmailObject = PHDB::findOne( $type , array("_id" =>new MongoId($_POST["id"])), array("email"));
              $memberEmail = $memberEmailObject['email'];
            }

            if(preg_match('#^[\w.-]+@[\w.-]+\.[a-zA-Z]{2,6}$#',$memberEmail))
            {
              
              $member = PHDB::findOne($memberType, array("email"=>$_POST['email']));
              
              if( !$member )
              {
                if($_POST['type'] == "persons"){
                 $member = array(
                 'name'=>$_POST['name'],
                 'email'=>$memberEmail,
                 'invitedBy'=>Yii::app()->session["userId"],
                 'created' => time(),
                 'links'=>array( 'events' => array($_POST["id"] =>array("type" => $type,
                                              "tobeconfirmed" => true,
                                              "invitedBy" => Yii::app()->session["userId"]
                                            )
                                  )
                      )
                 );
                  Person::createAndInvite($member);
                 } else {
                   $member = array(
                   'name'=>$_POST['name'],
                   'email'=>$memberEmail,
                   'invitedBy'=>Yii::app()->session["userId"],
                   'created' => time(),
                   'type'=>'Group',
                   'links'=>array( 'events' => array($_POST["id"] =>array("type" => $type,
                                              "tobeconfirmed" => true,
                                              "invitedBy" => Yii::app()->session["userId"]
                                            )
                                  )
                      )
                   );

                   Organization::createAndInvite($member);
                 }

                Link::connect($_POST["id"], PHType::TYPE_EVENTS,$member["_id"], $type, Yii::app()->session["userId"], "attendees" );
                $res = array("result"=>true,"msg"=>"Vos données ont bien été enregistré.","reload"=>true);

              }else{

                if( isset($event['links']["events"]) && isset( $event['links']["events"][(string)$member["_id"]] ))
                  $res = array( "result" => false , "content" => "member allready exists" );
                else {
                  Link::connect($member["_id"], $type, $_POST["id"], PHType::TYPE_EVENTS, Yii::app()->session["userId"], "events" );
                  Link::connect($_POST["id"], PHType::TYPE_EVENTS, $member["_id"], $type, Yii::app()->session["userId"], "attendees" );
                  $res = array("result"=>true,"msg"=>Yii::t("common","Your data has been saved!"),"reload"=>true);
                }
              }
            }else
              $res = array( "result" => false , "content" => "email must be valid" );
          }*/
        }
        //}
        Rest::json( $res );
    }
}