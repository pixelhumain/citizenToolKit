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
            "subject" => 'Nouvel utilisateur sur le site '.self::getAppName(),
            "from"=>Yii::app()->params['adminEmail'],
            "to" => Yii::app()->params['adminEmail'],
            "tplParams" => array(   "person"   => $person ,
                                    "title" => Yii::app()->name ,
                                    "logo"  => "/images/logoLTxt.jpg")
        );
        Mail::schedule($params);
    }
    public static function notifAdminNewPro($person) {
        $params = array(
            "type" => Cron::TYPE_MAIL,
            "tpl"=>'notifAdminNewPro',
            "subject" => 'Nouveau compte pro crée sur '.self::getAppName(),
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
                                    "invitorLogo" => @$invitor["profilThumbImageUrl"],
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
    public static function relaunchInvitePerson($person, $nameInvitor = null, $invitorUrl = null, $subject=null) {
        if(isset($person["invitedBy"]))
            $invitor = Person::getSimpleUserById($person["invitedBy"]);
        else if(isset($nameInvitor))
            $invitor["name"] = $nameInvitor ;
        
        if(@$invitor && empty(@$invitor["name"]))
            $subject = "Invitation:".$invitor["name"]. " attend votre réponse pour rejoindre ".self::getAppName().".";
        else
            $subject = "Invitation: n'attendez plus pour rejoindre le site ".self::getAppName().".";
        //if(empty($subject))
         //   $subject = $invitor["name"]. " vous invite à rejoindre ".self::getAppName().".";

        if(!@$person["email"] || empty($person["email"])){
            $getEmail=Person::getEmailById((string)$person["_id"]);
            $person["email"]=$getEmail["email"];
        }

        $params = array(
            "type" => Cron::TYPE_MAIL,
            "tpl"=>'relaunchInvitation',
            "subject" => $subject,
            "from"=>Yii::app()->params['adminEmail'],
            "to" => $person["email"],
            "tplParams" => array(   "invitorName"   => @$invitor["name"],
                                    "title" => self::getAppName() ,
                                    "logo" => Yii::app()->params["logoUrl"],
                                    "logo2" => Yii::app()->params["logoUrl2"],
                                    //"logo"=> "/images/logo-communecter.png",
                                    //"logo2" => "/images/logoLTxt.jpg",
                                    "invitedUserId" => $person["_id"])
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
            "subject" => '['.self::getAppName().'] - Venez rejoindre le réseau social citoyen',
            "from"=>Yii::app()->params['adminEmail'],
            "to" => $person["email"],
            "tplParams" => array(   "title" => self::getAppName() ,
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
            "subject" => 'Invited to '.self::getAppName().' by '.$name,
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
            "subject" => 'Réinitialisation du mot de passe pour le site '.self::getAppName(),
            "from"=>Yii::app()->params['adminEmail'],
            "to" => $email,
            "tplParams" => array(   "pwd"   => $pwd ,
                                    "title" => self::getAppName() ,
                                    "logo"  => "/images/logo-communecter.png",
                                    "logo2" => "/images/logoLTxt.jpg")
        );
        Mail::schedule($params);
    }

    public static function validatePerson( $person )
    {
        $params = array(
            "type" => Cron::TYPE_MAIL,
            "tpl"=>'validation', //TODO validation should be Controller driven boolean $this->userAccountValidation 
            "subject" => Yii::t("common","Confirm your account on ").self::getAppName(),
            "from" => Yii::app()->params['adminEmail'],
            "to" => $person["email"],
            "tplParams" => array( "user"  => $person["_id"] ,
                                  "title" => self::getAppName() ,
                                  "logo" => Yii::app()->params["logoUrl"],
                                     "logo2" => Yii::app()->params["logoUrl2"]
                                  //"urlRedirect" => Yii::app()->getRequest()->getBaseUrl(true);
                                  ) );
        Mail::schedule($params);
    }

    public static function newEvent( $creator, $newEvent )
    {
        $params = array(
            "type" => Cron::TYPE_MAIL,
            "tpl"=>'newEvent',
            "subject" => Yii::t("common",'New Event created on ').self::getAppName(),
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
            "subject" => Yii::t("common",'New Project created on ').self::getAppName(),
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
            "subject" => Yii::t("common",'New Organization created on ').self::getAppName(),
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
            "subject" => 'You have been invited to '.self::getAppName().' by '.$user["name"],
            "from"=>Yii::app()->params['adminEmail'],
            "to" => $person["email"],
            "tplParams" => array(   "invitorName"   => $user["name"],
                                    "title" => self::getAppName() ,
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
            "subject" => 'You received a new post on help and debug stream by '.self::getAppName(),
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
                "subject" => "[".self::getAppName()."] ".Yii::t("organization","A citizen ask to become ".$typeOfDemand." of")." ".$parent["name"],
                "from"=>Yii::app()->params['adminEmail'],
                "to" => $currentAdminEmail,
                "tplParams" => array(  "newPendingAdmin"=> $newPendingAdmin ,
                                        "title" => self::getAppName() ,
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
     * Send an email to the person invite by a member of an element 
     * @param array $parent datas of an element where person is inviting
     * @param array $newChild Datas of a person inviting
     * @param string $typeOfDemand gives the link definition between the parent and the child
     * @return null
     */
    public static function someoneInviteYouToBecome($parent, $parentType, $newChild, $typeOfDemand) {
        if($typeOfDemand=="admin")
            $verb="administrate";
        else{
            if($parentType==Event::COLLECTION)
                $verb="participate to";
            else if($parentType==Project::COLLECTION)
                $verb="contribute to";
            else
                $verb="join";
        }
        $childMail=Person::getEmailById((string)$newChild["_id"]);
        $params = array (
            "type" => Cron::TYPE_MAIL,
            "tpl"=>'inviteYouTo',
            "subject" => "[".self::getAppName()."] ".Yii::t("mail","Invitation to {what} {where}",array("{what}"=>Yii::t("mail",$verb),"{where}"=>$parent["name"])),    
            "from"=>Yii::app()->params['adminEmail'],       
            "to" => $childMail["email"],     
            "tplParams" => array(  
                "newChild"=> $newChild,      
                "title" => self::getAppName() , 
                "invitorName"=>Yii::app()->session["user"]["name"],   
                "invitorId" => Yii::app()->session["userId"],  
                "parent" => $parent,       
                "parentType" => $parentType,       
                "typeOfDemand"=> $typeOfDemand,
                "verb"=> $verb)     
        );   
        Mail::schedule($params);
    }
    /**

     * Send an email to the person when its request is confirmed
     * @param array $parent datas of an element where person is inviting
     * @param array $newChild Datas of a person inviting
     * @param string $typeOfDemand gives the link definition between the parent and the child
     * @return null
     */

    public static function someoneConfirmYouTo($parent, $parentType, $child, $typeOfDemand) {
        if($typeOfDemand=="admin")
            $verb="administrate";
        else{
            if($parentType==Event::COLLECTION)
                $verb="participate to";
            else if($parentType==Project::COLLECTION)
                $verb="contribute to";
            else
                $verb="join";
        }
        $childMail=Person::getEmailById((string)$child["_id"]);
        $params = array (
            "type" => Cron::TYPE_MAIL,
            "tpl"=>'confirmYouTo',
            "subject" => "[".self::getAppName()."] ".Yii::t("mail","Confirmation to ".$verb)." ".$parent["name"],    
            "from"=>Yii::app()->params['adminEmail'],       
            "to" => $childMail["email"],     
            "tplParams" => array(  
                "newChild"=> $child,      
                "title" => self::getAppName() , 
                "logo"=> Yii::app()->params["logoUrl"],
                "logo2" => Yii::app()->params["logoUrl2"],
                "authorName"=>Yii::app()->session["user"]["name"],   
                "authorId" => Yii::app()->session["userId"],  
                "parent" => $parent,       
                "parentType" => $parentType,       
                "typeOfDemand"=> $typeOfDemand,
                "verb"=> $verb)     
        );   
        Mail::schedule($params);
    }
    /**
     * Send an email to person or member when a follow is done on him or one of its elment
     * @param array $parent datas of an element where person is following
     * @param array $newChild Datas of a person inviting
     * @param string $typeOfDemand gives the link definition between the parent and the child
     * @return null
     */
    public static function follow($element, $elementType, $listOfMail=null) {
        if($elementType==Person::COLLECTION){
            $childMail=Person::getEmailById((string)$element["_id"]);
            $listOfMail=array($childMail["email"]);
            $title=Yii::t("mail","You have a new follower");
        }
        else
            $title=$element["name"].Yii::t("mail","has a new follower");
        foreach($listOfMail as $mail){
            $params = array (
                "type" => Cron::TYPE_MAIL,
                "tpl"=>'follow',
                "subject" => "[".self::getAppName()."] ".$title,    
                "from"=>Yii::app()->params['adminEmail'],       
                "to" => $mail,     
                "tplParams" => array(    
                    "title" => self::getAppName(), 
                    "logo"=> Yii::app()->params["logoUrl"],
                    "logo2" => Yii::app()->params["logoUrl2"],
                    "authorName"=>Yii::app()->session["user"]["name"],   
                    "authorId" => Yii::app()->session["userId"],  
                    "parent" => $element,       
                    "parentType" => $elementType)     
            );   
        }
        Mail::schedule($params);
    }

    /**
     * Send an email with beta test information
     * @return null
     */
    public static function sendMailFormContact($emailSender, $names, $subject, $contentMsg) {
        
        $params = array (
            "type" => Cron::TYPE_MAIL,
            "tpl"=>'contactForm',
            "subject" => $subject,
            "from" => Yii::app()->params['adminEmail'],
            "to"=>Yii::app()->params['adminEmail'],
            "tplParams" => array(   "title" => Yii::t("mail","New message from {who}",array("{who}"=>$names)),
                                    "subject" => $subject,
                                    "message" => $contentMsg,
                                    "emailSender" => $emailSender,
                                )
                                    /*   "logo"=> "/images/logo-communecter.png",
                                    "logo2" => "/images/logoLTxt.jpg")*/
        );   

        Mail::schedule($params);
    }


    /**
     * Send an email with beta test information
     * @return null
     */
    public static function sendMailFormContactPrivate($emailSender, $names, $subject, $contentMsg, $emailReceiver) {
        
        $params = array (
            "type" => Cron::TYPE_MAIL,
            "tpl"=>'contactForm',
            "subject" => $subject,
            "from" => Yii::app()->params['adminEmail'],
            "to"=>$emailReceiver,
            "tplParams" => array(   "title" => Yii::t("mail","New message from {who}",array("{who}"=>$names)),
                                    "subject" => $subject,
                                    "message" => $contentMsg,
                                    "emailSender" => $emailSender,
                                )
                                    /*   "logo"=> "/images/logo-communecter.png",
                                    "logo2" => "/images/logoLTxt.jpg")*/
        );   

        Mail::schedule($params);
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
            "subject" => "[".self::getAppName()."] - Plateforme en cours de test",
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
     * @param array $admins : a list of person to sent notifications
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

    public static function proposeInteropSource($url_source, $admins, $userID, $description) {

        $user = Person::getSimpleUserById($userID);

        // foreach ($admins as $id) {
        $aPerson = Person::getById("5880b24a8fe7a1a65b8b456b", false);
        if (!empty($aPerson["email"])) {
            $params = array (
                "type" => Cron::TYPE_MAIL,
                "tpl"=>'proposeInteropSource',
                "subject" => "[".self::getAppName()."] - Proposition de ".@$user["name"],
                "from"=>Yii::app()->params['adminEmail'],
                "to" => $aPerson["email"],
                "tplParams" => array(
                    "userName" => @$user["name"],
                    "description" => $description,
                    "source_de_donnees" => $url_source,
                    "logo"=> Yii::app()->params["logoUrl"],
                    "logo2" => Yii::app()->params["logoUrl2"],
                    // "url" => Yii::app()->getRequest()->getBaseUrl(true)."/".$url
                ),
            );
            
            Mail::schedule($params);
        }
        // }
    }   

    public static function validateProposedInterop($url_source, $userID, $adminID, $description) {

        $user = Person::getSimpleUserById($userID);
        $aPerson = Person::getSimpleUserById($adminID);

        // foreach ($admins as $id) {
        // $aPerson = Person::getById("5880b24a8fe7a1a65b8b456b", false);
        if (!empty($aPerson["email"])) {
            $params = array (
                "type" => Cron::TYPE_MAIL,
                "tpl"=>'validateInteropSource',
                "subject" => "[".self::getAppName()."] - Validation de votre proposition pour une nouvelle interopérabilité, ".@$user["name"],
                "from"=>Yii::app()->params['adminEmail'],
                "to" => $user["email"],
                "tplParams" => array(
                    "userName" => @$user["name"],
                    "url_source" => $url_source,
                    // "admin" => $aPerson['name'],
                    "description" => $description,
                    "logo"=> Yii::app()->params["logoUrl"],
                    "logo2" => Yii::app()->params["logoUrl2"],
                    // "url" => Yii::app()->getRequest()->getBaseUrl(true)."/".$url
                ),
            );
            
            Mail::schedule($params);
        }
        // }
    }   

    public static function rejectProposedInterop($url_source, $userID, $adminID, $description) {

        $user = Person::getSimpleUserById($userID);
        $aPerson = Person::getSimpleUserById($adminID);

        // foreach ($admins as $id) {
        // $aPerson = Person::getById("5880b24a8fe7a1a65b8b456b", false);
        if (!empty($aPerson["email"])) {
            $params = array (
                "type" => Cron::TYPE_MAIL,
                "tpl"=>'rejectInteropSource',
                "subject" => "[".self::getAppName()."] - Rejet de votre proposition pour une nouvelle interopérabilité, ".@$user["name"],
                "from"=>Yii::app()->params['adminEmail'],
                "to" => $user["email"],
                "tplParams" => array(
                    "userName" => @$user["name"],
                    "url_source" => $url_source,
                    // "admin" => $aPerson['name'],
                    "description" => $description,
                    "logo"=> Yii::app()->params["logoUrl"],
                    "logo2" => Yii::app()->params["logoUrl2"],
                    // "url" => Yii::app()->getRequest()->getBaseUrl(true)."/".$url
                ),
            );
            
            Mail::schedule($params);
        }
        // }
    }   

    private static function getAppName() {
        return isset(Yii::app()->params["name"]) ? Yii::app()->params["name"] : Yii::app()->name;       
    }

}