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
            $message->from = array($params['from'] => self::getAppName());

            return Yii::app()->mail->send($message);
        } else
            return false;
    }

    public static function notlocalServer() {
    	return (stripos($_SERVER['SERVER_NAME'], "127.0.0.1") === false && stripos($_SERVER['SERVER_NAME'], "localhost:8080") === false );
    }

    public static function schedule( $params, $update = null ) {
        Cron::save($params, $update);
    }

    public static function notifAdminNewUser($person) {
        $params = array(
            "type" => Cron::TYPE_MAIL,
            "tpl"=>'notifAdminNewUser',
            "subject" => 'Nouvel utilisateur sur le site '.self::getAppName(),
            "from"=>Yii::app()->params['adminEmail'],
            "to" => Yii::app()->params['adminEmail'],
            "tplParams" => array(   "person"   => $person ,
                                    "title" => self::getAppName() ,
                                    "logo"  => Yii::app()->params["logoUrl"])
        );
        Mail::schedule($params);
    }

    public static function invitePerson($person, $msg = null, $nameInvitor = null, $invitorUrl = null, $subject=null) {
        if(isset($person["invitedBy"]))
            $invitor = Person::getSimpleUserById($person["invitedBy"]);
        else if(isset($nameInvitor))
            $invitor["name"] = $nameInvitor ;

        if(empty($msg))
            $msg = $invitor["name"]. " vous invite à rejoindre ".self::getAppName().".";
		if(empty($subject))
            $subject = $invitor["name"]. " vous invite à rejoindre ".self::getAppName().".";

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
                                    "title" => self::getAppName() ,
                                    "logo" => Yii::app()->params["logoUrl"],
                                    "logo2" => Yii::app()->params["logoUrl2"],
                                    "invitedUserId" => $person["_id"],
                                    "message" => $msg)
        );

        if(!empty($invitorUrl))
            $params["tplParams"]["invitorUrl"] = $invitorUrl;

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
    
    public static function notifAddPersonInGroup($person, $group, $msg = null, $nameInvitor = null, $invitorUrl = null, $subject=null) {
        $invitor = Person::getSimpleUserById(Yii::app()->session["userId"]);


        if(empty($msg))
            $msg = $invitor["name"]. " vous a ajouté à ".$group["name"].".";
		if(empty($subject))
            $subject = self::getAppName() ." : ". $invitor["name"]. " vous a ajouté au groupe : ".$group["name"].".";

        if(!@$person["email"] || empty($person["email"])){
        	$getEmail=Person::getEmailById((string)$person["childId"]);
        	$person["email"]=$getEmail["email"];
        }

        $params = array(
            "type" => Cron::TYPE_MAIL,
            "tpl"=>'addPersonInGroup',
            "subject" => $subject,
            "from"=>Yii::app()->params['adminEmail'],
            "to" => $person["email"],
            "tplParams" => array(   "invitorName"   => $invitor["name"],
                                    "title" => self::getAppName() ,
                                    "logo" => Yii::app()->params["logoUrl"],
                                    "logo2" => Yii::app()->params["logoUrl2"],
                                    "invitedUserId" => $person["childId"],
                                    "groupName" => $group["name"],
                                    "message" => $msg)
        );

        if(!empty($invitorUrl))
            $params["tplParams"]["invitorUrl"] = $invitorUrl;

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
            "subject" => 'Réinitialisation du mot de passe sur '.self::getAppName(),
            "from"=>Yii::app()->params['adminEmail'],
            "to" => $email,
            "tplParams" => array(   "pwd"   => $pwd ,
                                    "title" => self::getAppName() ,
                                    "logo" => Yii::app()->params["logoUrl"],
                                    "logo2" => Yii::app()->params["logoUrl2"]
                                    )
        );
        Mail::schedule($params);
    }

    public static function validatePerson( $person )
    {
        $params = array(
            "type" => Cron::TYPE_MAIL,
            "tpl"=>'validation', //TODO validation should be Controller driven boolean $this->userAccountValidation
            "subject" => Yii::t("common","Welcome on ").self::getAppName(),
            "from" => Yii::app()->params['adminEmail'],
            "to" => $person["email"],
            "tplParams" => array( "user"  => $person["_id"] ,
                                  "username" => $person["username"] ,
                                  "pwd"   => $person["pwd"] ,
                                  "email" => $person["email"] ,
                                  "title" => self::getAppName() ,
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
                                        "logo"=> Yii::app()->params["logoUrl"],
                                        "logo2" => Yii::app()->params["logoUrl2"],
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

    /**
     * Send a email to a list of admins to ask them if they confirm the deletion
     * @param String $elementType : The element type
     * @param String $elementId : the element Id
     * @param String $reason : the reason why the element will be deleted
     * @param array $admins : a list of person to send notifications
     * @param String $userId : the userId asking the deletion
     * @return nothing
     */
    public static function confirmDeleteElement($elementType, $elementId, $reason, $admins, $userId) {
        $element = Element::getElementSimpleById($elementId, $elementType);
        $user = Person::getSimpleUserById($userId);
        $url = "#".Element::getControlerByCollection($elementType).".detail.id.".$element["_id"];
        $nbDayBeforeDelete = Element::NB_DAY_BEFORE_DELETE;
        foreach ($admins as $id) {
            $aPerson = Person::getById($id, false);
            if (!empty($aPerson["email"])) {
                $params = array (
                    "type" => Cron::TYPE_MAIL,
                    "tpl"=>'confirmDeleteElement',
                    "subject" => "[".self::getAppName()."] - Suppression de ".@$element["name"],
                    "from"=>Yii::app()->params['adminEmail'],
                    "to" => $aPerson["email"],
                    "tplParams" => array(
                        "elementType" => @$elementType,
                        "elementName" => @$element["name"],
                        "userName" => @$user["name"],
                        "logo"=> Yii::app()->params["logoUrl"],
                        "logo2" => Yii::app()->params["logoUrl2"],
                        "reason" => $reason,
                        "nbDayBeforeDelete" => $nbDayBeforeDelete,
                        "url" => Yii::app()->getRequest()->getBaseUrl(true)."/".$url),
                );

                Mail::schedule($params);
            }
        }
    }

    private static function getAppName() {
        return isset(Yii::app()->params["name"]) ? Yii::app()->params["name"] : Yii::app()->name;
    }

    private static function getMailUpdate($mail) {
    	$res = PHDB::findOne( Cron::COLLECTION, array("to" => $mail, "status" => Cron::STATUS_UPDATE) );
        return $res ;
    }

    public static function mailNotif($parentId, $parentType, $typeMsg, $params = null) {

        $element = Element::getElementById( $parentId, $parentType, null, array("links", "name") );
       
        foreach ($element["links"]["members"] as $key => $value) {
        	
        	$member = Element::getElementById( $key, Person::COLLECTION, null, array("email") );

        	if (!empty($member["email"])) {
        		$msg = "";
        		if($typeMsg == "news")
        			$msg = "Un Nouveau message a été ajouter pour " ;

        		$mail = Mail::getMailUpdate($member["email"]) ;

        		if(!empty($mail)){
        			$mail["tplParams"]["data"]["news"]++;
        			PHDB::update(Cron::COLLECTION,
						array("_id" => $mail["_id"]) , 
						array('$set' => array("tplParams" => $mail["tplParams"]))			
					);

        		}else{
        			$params = array (
	                    "type" => Cron::TYPE_MAIL,
	                    "tpl"=>'mailNotif',
	                    "subject" => "[".self::getAppName()."] - Nouveau message dans ".@$element["name"],
	                    "from"=>Yii::app()->params['adminEmail'],
	                    "to" => $member["email"],
	                    "tplParams" => array(
	                        "elementType" => $parentType,
	                        "elementName" => $element["name"],
	                        "userName" => @$user["name"],
	                        "logo"=> Yii::app()->params["logoUrl"],
	                        "logo2" => Yii::app()->params["logoUrl2"],
	                        "msg" => $msg,
	                        "data" => array("news" => 1),
	                    	"url" => Yii::app()->getRequest()->getBaseUrl(true)."/#element.detail.type.".$parentType.".id.".(String)$element["_id"] )
	                );


        			ActStr::VERB_POST => array(
						"repeat" => true,
						"type" => array(
							"targetIsAuthor" => array(
								"label"=>"{where} publishes a new post",
								"labelRepeat"=>"{where} publishes new posts"
							),
							"userWall" => array(
								"label"=>"{who} writes a post on your wall",
								"labelRepeat"=>"{who} write posts on your wall",
								"sameAuthor" => array(
									"labelRepeat" => "{who} writes posts on your wall"
								)
							),
							"label"=>"{who} writes a post on the wall of {where}",
							"labelRepeat"=>"{who} write posts on the wall of {where}",
							"sameAuthor" => array(
								"labelRepeat" => "{who} writes posts on the wall of {where}"
							)
						),
						"url" => "page/type/{collection}/id/{id}",
						"labelArray" => array("who", "where"),
						"icon" => "fa-rss"
					),



	                Mail::schedule($params, true);
        		}
        	}
        }
    }
}
