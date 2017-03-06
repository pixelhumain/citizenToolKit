<?php
/*
Contains anything generix for the site 
 */
class Mail {
    
    public static function send( $params, $force = false ) {
        $account = null;
        //Check if the user has the not valid email flag
        if (! empty($params['to'])) {
            if ($params['to'] != Yii::app()->params['adminEmail']) {
                $account = PHDB::findOne(Person::COLLECTION,array("email"=>$params['to']));
                if (!empty($account)) {
                    if (@$account["isNotValidEmail"]) {
                        $msg = "Try to send an email to a not valid email user : ".$params['to'];
                        return array("result" => false, "msg" => $msg);
                    } else if (@$account["status"] == "deleted") {
                        $msg = "Try to send an email to a deleted user : ".$params['to'];
                        return array("result" => false, "msg" => $msg);
                    }
                } else {
                    $msg = "Try to send an email to an unknown email user : ".$params['to'];
                    return array("result" => false, "msg" => $msg);
                }
            } else {
                $nameTo = "Admin Communecter";
            }
        } else {
            return false;
        }
        
        if ($account)
            $nameTo = $account["name"];

        if( PH::notlocalServer() || $force ){
            $message = new YiiMailMessage;
            $message->view =  $params['tpl'];
            $message->setSubject($params['subject']);
            $message->setBody($params['tplParams'], 'text/html');
            $message->addTo($params['to'], $nameTo);
            $message->from = array($params['from'] => "Communecter");

            return Yii::app()->mail->send($message);
        } else 
            return false;
    }

    public static function notlocalServer() {
    	return (stripos($_SERVER['SERVER_NAME'], "127.0.0.1") === false && stripos($_SERVER['SERVER_NAME'], "localhost:8080") === false );
    }

    public static function schedule( $params ) {
        Cron::save($params);
    }

    public static function notifAdminNewUser($person) {
        $params = array(
            "type" => Cron::TYPE_MAIL,
            "tpl"=>'notifAdminNewUser',
            "subject" => 'Nouvel utilisateur sur le site '.Yii::app()->name,
            "from"=>Yii::app()->params['adminEmail'],
            "to" => Yii::app()->params['adminEmail'],
            "tplParams" => array(   "person"   => $person ,
                                    "title" => Yii::app()->name ,
                                    "logo"  => "/images/logoLTxt.jpg")
        );
        Mail::schedule($params);
    }

    public static function invitePerson($person, $msg = null, $nameInvitor = null, $invitorUrl = null, $subject=null) {
        if(isset($person["invitedBy"]))
            $invitor = Person::getSimpleUserById($person["invitedBy"]);
        else if(isset($nameInvitor))
            $invitor["name"] = $nameInvitor ;
		
        if(empty($msg))
            $msg = $invitor["name"]. " vous invite à rejoindre ".Yii::app()-> name.".";
		if(empty($subject))
            $subject = $invitor["name"]. " vous invite à rejoindre ".Yii::app()-> name.".";

        if(!@$person["email"] || empty($person["email"])){
        	$getEmail=Person::getEmailById((string)$person["_id"]);
        	$person["email"]=$getEmail["email"];
        }

        $params = array(
            "type" => Cron::TYPE_MAIL,
            "tpl"=>'invitation',
            "subject" => $subject,
            "from"=>Yii::app()->params['adminEmail'],
            "to" => $person["email"],
            "tplParams" => array(   "invitorName"   => $invitor["name"],
                                    "title" => Yii::app()-> name ,
                                    "logo" => Yii::app()->params["logoUrl"],
                                    "logo2" => Yii::app()->params["logoUrl2"],
                                    //"logo"=> "/images/logo-communecter.png",
                                    //"logo2" => "/images/logoLTxt.jpg",
                                    "invitedUserId" => $person["_id"],
                                    "message" => $msg)
        );

        if(!empty($invitorUrl))
            $params["tplParams"]["invitorUrl"] = $invitorUrl;
        
        Mail::schedule($params);
    }

    //Mail is sent to super admin when an element is created
    public static function newElementMail($element, $collection, $creatorId, $listOfRecipient) {
        $subject = "[".Yii::app()->name."] - Création de ".$collection;
        $url = "#".Element::getControlerByCollection($collection).".detail.id.".$element["_id"];
        $creator = Person::getSimpleUserById($creatorId);
        $creatorName = isset($creator["name"]) ? $creator["name"] : "A.Nonyme";

        foreach ($listOfRecipient as $id => $recipient) {
            if (isset($recipient)) {
                $params = array(
                            "type" => Cron::TYPE_MAIL,
                            "tpl"=>'notifNewElement',
                            "subject" => $subject,
                            "from"=>Yii::app()->params['adminEmail'],
                            "to" => $recipient["email"],
                            "tplParams" => array(   "elementName"   => $element["name"],
                                                    "elementType"   => Element::getControlerByCollection($collection),
                                                    "elementCreator" => $creatorName,
                                                    "url" => Yii::app()->getRequest()->getBaseUrl(true)."/".$url,
                                                    "logo" => Yii::app()->params["logoUrl"],
                                                    "logo2" => Yii::app()->params["logoUrl2"],
                                                    "applicationName" => Yii::app()->params["name"],
                                                )
                        );
            }
        }
        Mail::schedule($params);
    }

	/*public static function invitePersonAgain($person, $msg = null, $nameInvitor = null, $invitorUrl = null) {
        $invitor = Person::getSimpleUserById(Yii::app()->session["userId"]);
        
            $invitor["name"] = $nameInvitor ;

        if(empty($msg))
            $msg = $invitor["name"]. " vous relance pour rejoindre Communecter.";

        

        $params = array(
            "type" => Cron::TYPE_MAIL,
            "tpl"=>'invitation',
            "subject" => '['.Yii::app()->name.'] - Vous avez été invité(e) par '.$invitor["name"],
            "from"=>Yii::app()->params['adminEmail'],
            "to" => $person["email"],
            "tplParams" => array(   "invitorName"   => $invitor["name"],
                                    "title" => Yii::app()->name ,
                                    "logo"=> "/images/logo-communecter.png",
                                    "logo2" => "/images/logoLTxt.jpg",
                                    "invitedUserId" => $person["_id"],
                                    "message" => $msg)
        );

        if(!empty($invitorUrl))
            $params["tplParams"]["invitorUrl"] = $invitorUrl;
        
        Mail::schedule($params);
    }*/
    /**
     * Invite bankers
     * @param array $person A well format person
     * @param boolean $isInvited : if the person is already in the db and already use the platform we adapt the mail
     * @return nothing
     */
    public static function inviteKKBB($person, $isInvited) {

        $params = array(
            "type" => Cron::TYPE_MAIL,
            "tpl"=>'inviteKKBB',
            "subject" => '['.Yii::app()->name.'] - Venez rejoindre le réseau social citoyen',
            "from"=>Yii::app()->params['adminEmail'],
            "to" => $person["email"],
            "tplParams" => array(   "title" => Yii::app()->name ,
                                    "logo"=> "/images/logo-communecter.png",
                                    "logo2" => "/images/logoLTxt.jpg",
                                    "invitedUserId" => $person["_id"],
                                    "isInvited" => $isInvited)
        );
        Mail::schedule($params);
    }

    //TODO SBAR - Do the template
    public static function newConnection($name, $mail, $newConnectionUserId) {
        $params = array(
            "type" => Cron::TYPE_MAIL,
            "tpl"=>'invitation',
            "subject" => 'Invited to '.Yii::app()->name.' by '.$name,
            "from"=>Yii::app()->params['adminEmail'],
            "to" => Yii::app()->params['adminEmail'],
            "tplParams" => array(   "sponsorName"   => $name ,
                                    "title" => Yii::app()->name ,
                                    "logo"  => "/images/logo.png")
        );
        Mail::schedule($params);
    }

    public static function passwordRetreive( $email, $pwd )
    {
        $params = array(
            "type" => Cron::TYPE_MAIL,
            "tpl"=>'passwordRetreive',
            "subject" => 'Réinitialisation du mot de passe pour le site '.Yii::app()->name,
            "from"=>Yii::app()->params['adminEmail'],
            "to" => $email,
            "tplParams" => array(   "pwd"   => $pwd ,
                                    "title" => Yii::app()->name ,
                                    "logo"  => "/images/logo-communecter.png",
                                    "logo2" => "/images/logoLTxt.jpg")
        );
        Mail::schedule($params);
    }

    public static function validatePerson( $person ) {
        $params = array(
            "type" => Cron::TYPE_MAIL,
            "tpl"=>'validation', //TODO validation should be Controller driven boolean $this->userAccountValidation 
            "subject" => Yii::t("common","Confirm your account on ").Yii::app()->name,
            "from" => Yii::app()->params['adminEmail'],
            "to" => $person["email"],
            "tplParams" => array( "user"  => $person["_id"] ,
                                  "title" => self::getAppName(),
                                  //"logo"  => "/images/logoLTxt.jpg" 
                                  "logo" => Yii::app()->params["logoUrl"],
                                  //"urlRedirect" => Yii::app()->getRequest()->getBaseUrl(true);
                                  ) );
        Mail::schedule($params);
    }

    public static function newEvent( $creator, $newEvent )
    {
        $params = array(
            "type" => Cron::TYPE_MAIL,
            "tpl"=>'newEvent',
            "subject" => Yii::t("common",'New Event created on ').Yii::app()->name,
            "from"=>Yii::app()->params['adminEmail'],
            "to" => $creator['email'],
            "tplParams" => array( "user"=> $creator['_id'] ,
                                   "title" => $newEvent['name'] ,
                                   "creatorName" => $creator['name'],
                                   "url"  => "#event.detail.id.".$newEvent["_id"] )
            );
        Mail::schedule($params);
    }

    public static function newProject( $creator, $newProject )
    {
        $params = array(
            "type" => Cron::TYPE_MAIL,
            "tpl"=>'newProject',
            "subject" => Yii::t("common",'New Project created on ').Yii::app()->name,
            "from"=>Yii::app()->params['adminEmail'],
            "to" => $creator['email'],
            "tplParams" => array( "user"=> $creator['_id'] ,
                                   "title" => $newProject['name'] ,
                                   "creatorName" => $creator['name'],
                                   "url"  => "#project.detail.id.".$newProject["_id"] )
            );
        Mail::schedule($params);
    }

    public static function newOrganization( $creator,$newOrganization )
    {
        $params = array (
            "type" => Cron::TYPE_MAIL,
            "tpl"=>'newOrganization',
            "subject" => Yii::t("common",'New Organization created on ').Yii::app()->name,
            "from"=>Yii::app()->params['adminEmail'],
            "to" => $newOrganization["email"],
            "tplParams" => array( "user"=> $creator['_id'] ,
                                   "title" => $newOrganization['name'] ,
                                   "creatorName" => $creator['name'],
                                   "url"  => "#organization.dashboard.id.".$newOrganization["_id"] )
        );
        Mail::schedule($params);
    }


    public static function inviteContact($mailContact, $user) {
        $params = array(
            "type" => Cron::TYPE_MAIL,
            "tpl"=>'invitation',
            "subject" => 'You have been invited to '.Yii::app()->name.' by '.$user["name"],
            "from"=>Yii::app()->params['adminEmail'],
            "to" => $person["email"],
            "tplParams" => array(   "invitorName"   => $user["name"],
                                    "title" => Yii::app()->name ,
                                    "logo"  => "/images/logo.png",
                                    "invitedUserId" => $person["_id"])
        );
        Mail::schedule($params);
    }
	/**
	* Send an email to contact@pixelhumain.com quand quelqu'un post dans les news help and bugs	
	* @param string $text message of user
	*/
	 public static function notifAdminBugMessage($text) {
        $params = array(
            "type" => Cron::TYPE_MAIL,
            "tpl"=>'helpAndDebugNews',
            "subject" => 'You received a new post on help and debug stream by '.Yii::app()->name,
            "from"=>Yii::app()->session['userEmail'],
            "to" => Yii::app()->params['adminEmail'],
            "tplParams" => array(
                                    "title" => Yii::app()->session["userName"] ,
                                    "logo"  => "/images/logo.png",
                                    "content"=> $text)
        );
        Mail::schedule($params);
    }

    /**
     * Send an email when some one ask to become an admin of an organization to the current admins
     * @param array $organization datas of an organization
     * @param array $newPendingAdmin Datas of a person asking to become an admin
     * @param array $listofAdminsEmail array of email to send to
     * @return null
     */
    public static function someoneDemandToBecome( $parent, $parentType, $newPendingAdmin, $listofAdminsEmail, $typeOfDemand) {
       
       foreach ($listofAdminsEmail as $currentAdminEmail) {
           $params = array (
                "type" => Cron::TYPE_MAIL,
                "tpl"=>'askToBecomeAdmin',
                "subject" => "[".Yii::app()->name."] ".Yii::t("organization","A citizen ask to become ".$typeOfDemand." of")." ".$parent["name"],
                "from"=>Yii::app()->params['adminEmail'],
                "to" => $currentAdminEmail,
                "tplParams" => array(  "newPendingAdmin"=> $newPendingAdmin ,
                                        "title" => Yii::app()->name ,
                                        "parent" => $parent,
                                        "parentType" => $parentType,
                                        "typeOfDemand"=> $typeOfDemand)
            );   
            Mail::schedule($params);
        }
    }

    /**
     * Send an email with beta test information
     * @return null
     */
    public static function betaTestInformation($person) {
		$email = $person['email'];

		$params = array (
			"type" => Cron::TYPE_MAIL,
			"tpl"=>'betaTest',
			"subject" => "[".Yii::app()->name."] - Plateforme en cours de test",
			"from"=>Yii::app()->params['adminEmail'],
			"to" => $email,
			"tplParams" => array(   "logo"=> "/images/logo-communecter.png",
			                        "logo2" => "/images/logoLTxt.jpg")
		);   

		Mail::schedule($params);
    }

    private static function getAppName() {
        return isset(Yii::app()->params["name"]) ? Yii::app()->params["name"] : Yii::app()->name;       
    }

}