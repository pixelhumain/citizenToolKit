<?php
class InvitationAction extends CAction
{
    public function run()
    {
    	$res = array( "result" => false , "content" => Yii::t("common", "Something went wrong!" ));
		 if(Yii::app()->request->isAjaxRequest && isset( $_POST["parentId"]) )
		 {
		 	//test if group exist
			$organization = (isset($_POST["parentId"])) ? PHDB::findOne( Organization::COLLECTION,array("_id"=>new MongoId($_POST["parentId"]))) : null;
			$citoyen = (isset($_POST["parentId"])) ? PHDB::findOne( Person::COLLECTION,array("_id"=>new MongoId($_POST["parentId"]))) : null;
			if($citoyen || $organization)
			{
				$memberEmail = $_POST['email'];
				if($citoyen){
					$type =  Person::COLLECTION;
				}
				else if($organization){
					$type = Organization::COLLECTION;
				}
				
				if(isset($_POST["id"]) && $_POST["id"] != ""){
					$memberEmailObject = PHDB::findOne( $type , array("_id" =>new MongoId($_POST["id"])), array("email"));
					$memberEmail = $memberEmailObject['email'];
				}

			 	//check citizen exist by email
			 	if(preg_match('#^[\w.-]+@[\w.-]+\.[a-zA-Z]{2,6}$#',$memberEmail))
				{
					$member = PHDB::findOne( $type , array("email"=>$memberEmail));
					if( !$member )
					{
						 //create an entry in the citoyens colelction
						 $member = array(
						 'name'=>$_POST['name'],
						 'email'=>$memberEmail,
						 'created' => time(),
						 'type'=>'citoyen',
						 "links" => array( 
						 	'knows'=>array( $_POST["parentId"] => array( "type" => $type) ),
						 	'invitedBy'=>array(Yii::app()->session["userId"] => array( "type" => $type)),
						 	),
						 );
						Person::createAndInvite($member);
						 //add the member into the organization map
						$member = PHDB::findOne( Person::COLLECTION , array("email"=>$memberEmail));
						PHDB::update( $type, 
								array("_id" => new MongoId($_POST["parentId"])) ,
								array('$set' => array( "links.knows.".(string)$member["_id"].".type" => $type ) ));
						$res = array("result"=>true,"msg"=>Yii::t("common", "Your data has been saved"),"reload"=>true);
						 //TODO : background send email
						 //send validation mail
						 //TODO : make emails as cron jobs
						 /*$message = new YiiMailMessage;
						 $message>view = 'invitation';
						 $name = (isset($sponsor["name"])) ? "par ".$sponsor["name"] : "par ".$sponsor["email"];
						 $message>setSubject('Invitation au projet Pixel Humain '.$name);
						 $message>setBody(array("user"=>$member["_id"],
						 "sponsorName"=>$name), 'text/html');
						 $message>addTo("oceatoon@gmail.com");//$_POST['email']
						 $message>from = Yii::app()>params['adminEmail'];
						Yii::app()>mail>send($message);*/

						 //TODO : add an admin notification
						 Notification::saveNotification(array("type"=>NotificationType::NOTIFICATION_INVITATION,
						 "user"=>Yii::app()->session["userId"],
						 "invited"=>$member["_id"]));
						 
					}
					else
					{
					 //person exists with this email and is connected to this Organisation
						$memberType = "citoyens";
						if( isset($citoyen['links']["knows"]) && isset( $organization['links']["knows"][(string)$member["_id"]] ))

							$res = array( "result" => false , "content" => "allready in your contact" );
						else {
							PHDB::update( $type , array( "email" => $memberEmail) ,
								array('$set' => array( "links.knows.".$_POST["parentId"].".type" => "citoyens" ) ));
								
							PHDB::update( $type , 
								array("_id" => new MongoId($_POST["parentId"])) ,
								array('$set' => array( "links.knows.".(string)$member["_id"].".type" => $memberType  ) ));
							$res = array("result"=>true,"msg"=>"Vos données ont bien été enregistré.","reload"=>true);	
						}	
					}
				} else
				$res = array( "result" => false , "content" => "email must be valid" );
			}
		 }
		 Rest::json( $res );
    }
}