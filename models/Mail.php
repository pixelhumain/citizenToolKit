<?php
/*
Contains anything generix for the site
 */
class Mail {

    public static $mailTree = array( 
                        ActStr::VERB_POST => array(
                            "url" => "page/type/{collection}/id/{id}",
                            "labelArray" => array("who", "where"),
                            "label"=>"{who} wrote a message :",
                            "icon" => "fa-rss" ),
                        ActStr::VERB_ADD => array(
                            "type" => array(
                                Poi::COLLECTION=> array(
                                    "url" => "page/type/{objectType}/id/{objectId}",
                                    "label" => "{who} added a new production : {what}"
                                )
                            ),
                            "labelArray" => array("who","where","what"),
                            "icon" => "fa-plus"
                        ),
                        ActStr::VERB_COMMENT => array(
                            "type" => array(
                                Poi::COLLECTION => array(
                                    "label" => "{who} commented :",
                                ),
                                Comment::COLLECTION => array(
                                    "label" => "{who} answered to a comment posted",
                                ),
                                News::COLLECTION => array(
                                    "label" => "{who} answered to a new posted",
                                )
                            ),
                            "labelArray" => array("who","where","what"),
                            "mail" => array(
                                "type"=>"instantly",
                                "to" => "author" //If orga or project to members
                            ),
                            "icon" => "fa-comment"
                            //"url" => "{whatController}/detail/id/{whatId}"
                        ),
                    );

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
        //Rest::json($person); exit ;

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
                                    "invitedUserId" => Yii::app()->session["userId"],
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

    public static function mailNotif($parentId, $parentType, $paramsMail = null) {
        // var_dump($parentId);
        // var_dump($parentType);
        // var_dump($paramsMail);exit;
        $element = Element::getElementById( $parentId, $parentType, null, array("links", "name") );
       
        foreach ($element["links"]["members"] as $key => $value) {
        	

            if ($key != Yii::app()->session["userId"]) {

            	$member = Element::getElementById( $key, Person::COLLECTION, null, array("email","preferences","roles") );

            	if (!empty($member["email"]) && 
                    !empty($member["preferences"]) && 
                    !empty($member["preferences"]["mailNotif"]) &&
                    $member["preferences"]["mailNotif"] == true ) {

                    
                    
            		$mail = Mail::getMailUpdate($member["email"]) ;
            		if(!empty($mail)){

                        $paramTpl = self::createParamsTpl($paramsMail, $mail["tplParams"]["data"]);
                        // var_dump($paramTpl); exit ;
                        $mail["tplParams"]["data"] = $paramTpl ;
            			PHDB::update(Cron::COLLECTION,
    						array("_id" => $mail["_id"]) , 
    						array('$set' => array("tplParams" => $mail["tplParams"]))			
    					);

            		}else{
                        $paramTpl = self::createParamsTpl($paramsMail, null);
                        // var_dump($paramTpl); exit ;
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
    	                        "data" => $paramTpl)
    	                );

                        Mail::schedule($params, true);
            		}
            	}
            }
        }
    }


    public static function createParamsMails($verb, $target = null, $object = null, $author = null){
        $paramsMail = Mail::$mailTree[$verb];
        // var_dump($verb); var_dump($target["type"]); exit ;
        if($verb == ActStr::VERB_ADD){
            if(!empty($paramsMail["type"][$object["type"]])){
                $type = $paramsMail["type"][$object["type"]];
                // var_dump($target["type"]);
                // var_dump($paramsMail["type"][$target["type"]]); exit ;
                unset($paramsMail["type"][$object["type"]]);
                $paramsMail = array_merge($paramsMail, $type);
            }
        }else{
            if(!empty($paramsMail["type"][$target["type"]])){

                $type = $paramsMail["type"][$target["type"]];
                // var_dump($target["type"]);
                // var_dump($paramsMail["type"][$target["type"]]); exit ;
                unset($paramsMail["type"][$target["type"]]);
                $paramsMail = array_merge($paramsMail, $type);
            }
        }
        

        $paramsMail["verb"] = $verb;
        $paramsMail["target"]=$target;
        $paramsMail["object"]=$object;
        $paramsMail["author"]=$author;
        //$paramsMail["levelType"]=$levelType;

        //var_dump($paramsMail); exit;
        return $paramsMail;
    }


    public static function createParamsTpl($paramsMail, $paramTpl = null){
        //Rest::json($paramsMail); exit ;
        $targetType = $paramsMail["target"]["type"];
        $targetId = $paramsMail["target"]["id"];
        $verb = $paramsMail["verb"];


        if(empty($paramTpl))
            $paramTpl = array();

        if(empty($paramTpl[$targetType]))
            $paramTpl[$targetType] = array();

        if(empty($paramTpl[ $targetType ][ $targetId ])){

            $paramTpl[ $targetType ][ $targetId ] = array( "url" => Yii::app()->getRequest()->getBaseUrl(true)."/#element.detail.type.".$targetType.".id.".$targetId,
                                                            "name" => $paramsMail["target"]["name"]  ) ;
        }

        if(empty($paramTpl[ $targetType ][ $targetId ][ $verb ]))
            $paramTpl[ $targetType ][ $targetId ][ $verb ] = array();
        
        $paramLabel = array();

        foreach ($paramsMail["labelArray"] as $key => $value) {
            if("who" == $value && !empty($paramsMail[ "author" ]) ){
                $url = Yii::app()->getRequest()->getBaseUrl(true)."/#element.detail.type.".$paramsMail[ "author" ][ "type" ].".id.".$paramsMail[ "author" ][ "id" ] ;
                $str = '<a href="'.$url.'" >'.$paramsMail[ "author" ][ "name" ]."</a>";
                //$paramLabel["{".$value."}"] = $str;
            }
            else if("where" == $value && !empty($paramsMail[ "target" ]) ){
                $url = Yii::app()->getRequest()->getBaseUrl(true)."/#element.detail.type.".$paramsMail[ "target" ][ "type" ].".id.".$paramsMail[ "target" ][ "id" ] ;
                $str = '<a href="'.$url.'" >'.$paramsMail[ "target" ][ "name" ]."</a>";
                //$paramLabel["{".$value."}"] = $paramsMail[ "target" ][ "name" ];
                // $paramLabel["{".$value."}"]["url"] = Yii::app()->getRequest()->getBaseUrl(true)."/#element.detail.type.".$paramsMail[ "target" ][ "type" ].".id.".$paramsMail[ "target" ][ "id" ] ;
            }
            else if("what" == $value && !empty($paramsMail[ "object" ])){
                $url = Yii::app()->getRequest()->getBaseUrl(true)."/#element.detail.type.".$paramsMail[ "object" ][ "type" ].".id.".$paramsMail[ "object" ][ "id" ] ;
                $str = '<a href="'.$url.'" >'.$paramsMail[ "object" ][ "name" ]."</a>";
                //$paramLabel["{".$value."}"] = $paramsMail[ "object" ][ "name" ];
                // $paramLabel["{".$value."}"]["url"] = Yii::app()->getRequest()->getBaseUrl(true)."/#element.detail.type.".$paramsMail[ "object" ][ "type" ].".id.".$paramsMail[ "object" ][ "id" ] ;
            }

            $paramLabel["{".$value."}"] = $str;
        }
       

        $info["text"] = Yii::t("mail", $paramsMail["label"], $paramLabel);


        if( ( $verb == ActStr::VERB_COMMENT || $verb == ActStr::VERB_POST ) && !empty($paramsMail["target"]["value"] ) ) {
            $info["value"] = $paramsMail["target"]["value"] ;
        }

        $paramTpl[ $targetType ][ $targetId ][ $verb ][] = $info ;

        return $paramTpl ;

    }
}
