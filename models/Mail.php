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

    // public static function schedule( $params ) {
    //     Cron::save($params);
    // }

    public static function schedule( $params, $update = null ) {
        Cron::save($params, $update);
    }

    public static function notifAdminNewUser($person) {
        


        $mail = Mail::getMailUpdate(Yii::app()->params['adminEmail'], 'notifAdminNewUser') ;

    	if(!empty($mail)){
            $mail["tplParams"]["data"][] = $person;
            PHDB::update(Cron::COLLECTION,
                array("_id" => $mail["_id"]) , 
                array('$set' => array("tplParams" => $mail["tplParams"]))           
            );
        }else{
        	$data[] = $person ;
    		$params = array(
	            "type" => Cron::TYPE_MAIL,
	            "tpl"=>'notifAdminNewUser',
	            "subject" => Yii::t("mail",'New user on {website}',array("{website}"=>self::getAppName())),
	            "from"=>Yii::app()->params['adminEmail'],
	            "to" => Yii::app()->params['adminEmail'],
	            "tplParams" => array(   "data"   => $data ,
	                                    "title" => Yii::app()->name ,
	                                    "logo"  => "/images/logoLTxt.jpg")
	        );
	        Mail::schedule($params, true);
    	}
    }
    public static function referenceEmailInElement($collection, $id, $email){
        $element=Element::getElementSimpleById($id, $collection, null, array("name"));
        $params = array(
            "type" => Cron::TYPE_MAIL,
            "tpl"=>"referenceEmailInElement",
            "subject" => Yii::t("mail","{who} added your contact in {where} on {website}", array("{who}"=>Yii::app()->session["user"]["name"], "{where}"=>Yii::t("common","the ".Element::getControlerByCollection($collection))." ".$element["name"] ,"{website}"=>self::getAppName())),
            "from"=>Yii::app()->params['adminEmail'],
            "to" => $email,
            "tplParams" => array(   "collection"   => $collection ,
                                    "id"   => $id,
                                    "name" => $element["name"],
                                    "invitorId"=>Yii::app()->session["userId"],
                                    "invitorName"=>Yii::app()->session["user"]["name"],
                                    "title" => Yii::app()->name ,
                                    "language" => Yii::app()->language
                                    )
        );
        $params=self::getCustomMail($params);
        Mail::schedule($params);
    }
    public static function notifAdminNewPro($person) {

    	$params = array(
            "type" => Cron::TYPE_MAIL,
            "tpl"=>'notifAdminNewPro',
            "subject" => Yii::t("mail",'New professional account on {website}',array("{website}"=>self::getAppName())),
            "from"=>Yii::app()->params['adminEmail'],
            "to" => Yii::app()->params['adminEmail'],
            "tplParams" => array(   "person"   => $person ,
                                    "title" => Yii::app()->name ,
                                    "logo"  => "/images/logoLTxt.jpg")
        );
        Mail::schedule($params);
    	
        
        
    }
    public static function notifAdminNewReservation($params){
        $params = array(
            "type" => Cron::TYPE_MAIL,
            "tpl"=>'reservations',
            "subject" => Yii::t("terla",'Réservation de '.Yii::app()->session["user"]["name"].' pour le circuit '.$params["name"]),
            "from"=>Yii::app()->params['adminEmail'],
            "to" => Yii::app()->params['adminEmail'],
            "tplParams" => array(   "order"   => $params ,
                                    "title" => Yii::t("terla",'Réservation de '.Yii::app()->session["user"]["name"].' pour le circuit '.$params["name"]) ,
                                    "logo" => Yii::app()->params["logoUrl"],
                                    "logo2" => Yii::app()->params["logoUrl2"]
            )
        );
        Mail::schedule($params);
    }
    public static function invitePerson($person, $msg = null, $nameInvitor = null, $invitorUrl = null, $subject=null) {
        if(isset($person["invitedBy"]))
            $invitor = Person::getSimpleUserById($person["invitedBy"]);
        else if(isset($nameInvitor))
            $invitor["name"] = $nameInvitor ;
		
        // if(empty($msg))
        //     $msg = $invitor["name"]. " vous invite à rejoindre ".self::getAppName().".";
		
        if(empty($subject)){
            //$subject = $invitor["name"]. " vous invite à rejoindre ".self::getAppName().".";
            if(@$invitor && empty(@$invitor["name"]))
                $subject = Yii::t("mail", "{who} is waiting for you on {what}", array("{who}"=>$invitor["name"], "{what}"=>self::getAppName()));
            else
                $subject = Yii::t("mail", "{what} is waiting for you", array( "{what}"=>self::getAppName() ) ) ;
        }

        

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
                                    "invitedUserId" => $person["_id"],
                                    "message" => $msg,
                                    "language" => Yii::app()->language )
        );

        $params=self::getCustomMail($params);
        if(!empty($invitorUrl))
            $params["tplParams"]["invitorUrl"] = $invitorUrl;
        
        Mail::schedule($params);
    }
    public static function relaunchInvitePerson($person, $nameInvitor = null, $invitorUrl = null, $subject=null) {
        if(isset($person["invitedBy"]))
            $invitor = Person::getSimpleUserById($person["invitedBy"]);
        else if(isset($nameInvitor)){
            $invitor["name"] = $nameInvitor ;
            // var_dump($invitor["name"]);
        }
        

        if(@$invitor && empty(@$invitor["name"]))
            $subject = Yii::t("mail", "{who} is waiting for you on {what}", array("{who}"=>$invitor["name"], "{what}"=>self::getAppName()));
        else
            $subject = Yii::t("mail", "{what} is waiting for you", array("{what}"=>self::getAppName()));


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
                                    "invitedUserId" => $person["_id"],
                                    "language" => ( !empty($person["language"]) ? $person["language"] : "fr" ) )
        );
        $params=self::getCustomMail($params);
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
            "subject" => Yii::t("mail","Retreive your password on {website}", array("{website}"=>self::getAppName())),
            "from"=>Yii::app()->params['adminEmail'],
            "to" => $email,
            "tplParams" => array(   "pwd"   => $pwd ,
                                    "title" => self::getAppName() 
                                    )
        );
        $params=self::getCustomMail($params);
        Mail::schedule($params);
    }

    public static function validatePerson( $person )
    {
        $params = array(
            "type" => Cron::TYPE_MAIL,
            "tpl"=>'validation', //TODO validation should be Controller driven boolean $this->userAccountValidation 
            "subject" => Yii::t("mail","Confirm your account on {website}", array("{website}"=>self::getAppName())),
            "from" => Yii::app()->params['adminEmail'],
            "to" => $person["email"],
            "tplParams" => array( "user"  => $person["_id"] ,
                                  "title" => self::getAppName() 
                                  ) );
        $params=self::getCustomMail($params);
        Mail::schedule($params);
    }
    //TODO QUESTION BOUBOULE => TO DELETE ... 
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
    //TODO QUESTION BOUBOULE => TO DELETE ... 
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
    //TODO QUESTION BOUBOULE => TO DELETE ... 
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
            "subject" => Yii::t("mail", 'You have been invited to {website} by {who}', array("{website}"=>self::getAppName(),"{who}"=>$user["name"])),
            "from"=>Yii::app()->params['adminEmail'],
            "to" => $person["email"],
            "tplParams" => array(   "invitorName"   => $user["name"],
                                    "title" => self::getAppName() ,
                                    "logo"  => "/images/logo.png",
                                    "invitedUserId" => $person["_id"])
        );
        Mail::schedule($params);
    }

    //TODO QUESTION BOUBOULE : TO DELETE OR IMPROVE PROCESS
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
                "subject" => "[".self::getAppName()."] ".Yii::t("mail","A citizen ask to become {what} of {where}", 
                    array("{what}"=>$typeOfDemand, "{where}"=>$parent["name"])),
                "from"=>Yii::app()->params['adminEmail'],
                "to" => $currentAdminEmail,
                "tplParams" => array(  "newPendingAdmin"=> $newPendingAdmin ,
                                        "title" => self::getAppName() ,
                                        "parent" => $parent,
                                        "parentType" => $parentType,
                                        "typeOfDemand"=> $typeOfDemand)
            );  
            $params=self::getCustomMail($params); 
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
        $params=self::getCustomMail($params);
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
            "subject" => "[".self::getAppName()."] ".Yii::t("mail","Confirmation to {what} {where}", 
                array("{what}"=>$verb, "{where}"=>$parent["name"])),    
            "from"=>Yii::app()->params['adminEmail'],       
            "to" => $childMail["email"],     
            "tplParams" => array(  
                "newChild"=> $child,      
                "title" => self::getAppName() , 
                "authorName"=>Yii::app()->session["user"]["name"],   
                "authorId" => Yii::app()->session["userId"],  
                "parent" => $parent,       
                "parentType" => $parentType,       
                "typeOfDemand"=> $typeOfDemand,
                "verb"=> $verb)     
        );
        $params=self::getCustomMail($params);   
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
                    "authorName"=>Yii::app()->session["user"]["name"],   
                    "authorId" => Yii::app()->session["userId"],  
                    "parent" => $element,       
                    "parentType" => $elementType)     
            );   
            $params=self::getCustomMail($params);
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
        if(@Yii::app()->session["custom"] && @Yii::app()->session["custom"]["title"])
            $appName=Yii::app()->session["custom"]["title"];
        else if(@Yii::app()->params["name"])
            $appName=Yii::app()->params["name"];
        else
            $appName=Yii::app()->name;
        return $appName;       
    }


	public static function mailMaj() {

		$persons=PHDB::find(Person::COLLECTION, array("pending"=>array('$exists'=>0), "email"=>array('$exists'=>1)), array("name", "language", "email"));
		$i=0;
		$v=0;
		$languageUser = Yii::app()->language;
		$res = array();
		foreach($persons as $key => $value){
			if(!empty($value["email"]) && DataValidator::email($value["email"])=="" && !empty($value["language"])){
				echo $key." : ".$value["name"]." : ".$value["language"]." <br/> ";
				// Yii::app()->language = $value["language"];
				$subject = Yii::t("mail", "New Update");
		        $params = array(
		            "type" => Cron::TYPE_MAIL,
		            "tpl"=>'update',
		            "subject" => $subject,
		            "from"=>Yii::app()->params['adminEmail'],
		            "to" => $value["email"],
		            "tplParams" => array(   "title" => self::getAppName() ,
		                                    "logo" => Yii::app()->params["logoUrl"],
		                                    "logo2" => Yii::app()->params["logoUrl2"],
		                                    "invitedUserId" => $value["_id"],
		                                    "language" => ( !empty($value["language"]) ? $value["language"] : "fr" ) )
		        );
		        Mail::schedule($params);
				$i++;
			}else{
				$v++;
			}
		}
		echo $i." mails envoyé pour relancer l'inscription<br>";
		echo $v." utilisateur non inscrit (validé) qui ont un mail de marde<br>";

		Yii::app()->language = $languageUser ;

	}

    public static function confirmSavingSurvey($user, $survey) {

        //$languageUser = Yii::app()->language;
        $subject = Yii::t("surveys", "{what} Your application package is well submited", array("{what}"=> "[".$survey["title"]."]"));
        $params = array(
            "type" => Cron::TYPE_MAIL,
            "tpl"=>'survey.submissionSuccess',
            "subject" => $subject,
            "from"=>Yii::app()->params['adminEmail'],
            "to" => $user["email"],
            "tplParams" => array(   "title" => $survey["title"] ,
                                    "logo" => Yii::app()->createUrl('/'.$survey["urlLogo"]),
                                    "logo2" => Yii::app()->createUrl('/'.$survey["urlLogo"]),
                                    "user" => $user,
                                    "language" => Yii::app()->language,
                                    "survey"=>$survey
                                    )
        );
        Mail::schedule($params);
    }

    public static function sendNewAnswerToAdmin($email, $user, $survey) {

        //$languageUser = Yii::app()->language;
        $subject = Yii::t("surveys", "{what} A new application package is added", array("{what}"=> "[".$survey["title"]."]"));
        $params = array(
            "type" => Cron::TYPE_MAIL,
            "tpl"=>'survey.newSubmission',
            "subject" => $subject,
            "from"=>Yii::app()->params['adminEmail'],
            "to" => $email,
            "tplParams" => array(   "title" => $survey["title"] ,
                                    "logo" => Yii::app()->createUrl('/'.$survey["urlLogo"]),
                                    "logo2" => Yii::app()->createUrl('/'.$survey["urlLogo"]),
                                    "user" => $user,
                                    "language" => Yii::app()->language,
                                    "survey"=>$survey
                                )
        );
        Mail::schedule($params);
    }
   
	private static function getMailUpdate($mail, $tpl ) {
    	$res = PHDB::findOne( Cron::COLLECTION, array("tpl" => $tpl, "to" => $mail, "status" => Cron::STATUS_UPDATE) );
        return $res ;
    }


    public static function mailNotif($parentId, $parentType, $paramsMail = null) {
        // var_dump($parentId);
        // var_dump($parentType);
        //var_dump($paramsMail);exit;
        $element = Element::getElementById( $parentId, $parentType, null, array("links", "name") );
       
        foreach ($element["links"]["members"] as $key => $value) {
            
            if ($key != Yii::app()->session["userId"]) {

                $member = Element::getElementById( $key, Person::COLLECTION, null, array("email","preferences") );

                if (!empty($member["email"]) && 
                    !empty($member["preferences"]) && 
                    !empty($member["preferences"]["mailNotif"]) &&
                    $member["preferences"]["mailNotif"] == true ) {
                    
                    $mail = Mail::getMailUpdate($member["email"], 'mailNotif') ;
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
                                "data" => $paramTpl)
                        );
                        $params=self::getCustomMail($params);
                        Mail::schedule($params, true);
                    }
                }
            }
        }
    }

    public static function getCustomMail($params){
        if(@Yii::app()->session["custom"] && @Yii::app()->session["custom"]["logo"]){
            $params["tplParams"]["logo"]=Yii::app()->session["custom"]["logo"];
            $params["tplParams"]["logo2"]="";
            $params["tplParams"]["logoHeader"]=Yii::app()->session["custom"]["logo"];
        }else{
            $params["tplParams"]["logo"]=Yii::app()->params["logoUrl"];
            $params["tplParams"]["logo2"]=Yii::app()->params["logoUrl2"];
        }
        if(@Yii::app()->session["custom"] && @Yii::app()->session["custom"]["title"])
            $params["tplParams"]["title"]=Yii::app()->session["custom"]["title"];
        if(@Yii::app()->session["custom"] && @Yii::app()->session["custom"]["url"])
            $params["tplParams"]["url"]=Yii::app()->session["custom"]["url"];

        if( @Yii::app()->session["custom"] && 
            @Yii::app()->session["custom"]["mail"] && 
            @Yii::app()->session["custom"]["mail"][$params["tpl"]])
            $params["tplParams"] = array_merge($params["tplParams"], Yii::app()->session["custom"]["mail"][$params["tpl"]]);
        return $params;

    }
    
    public static function createParamsMails($verb, $target = null, $object = null, $author = null){
        $paramsMail = Mail::$mailTree[$verb];

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

        return $paramsMail;
    }


    public static function createParamsTpl($paramsMail, $paramTpl = null){
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
            }
            else if("where" == $value && !empty($paramsMail[ "target" ]) ){
                $url = Yii::app()->getRequest()->getBaseUrl(true)."/#element.detail.type.".$paramsMail[ "target" ][ "type" ].".id.".$paramsMail[ "target" ][ "id" ] ;
                $str = '<a href="'.$url.'" >'.$paramsMail[ "target" ][ "name" ]."</a>";
            }
            else if("what" == $value && !empty($paramsMail[ "object" ])){
                $url = Yii::app()->getRequest()->getBaseUrl(true)."/#element.detail.type.".$paramsMail[ "object" ][ "type" ].".id.".$paramsMail[ "object" ][ "id" ] ;
                $str = '<a href="'.$url.'" >'.$paramsMail[ "object" ][ "name" ]."</a>";
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


    public static function bookmarkNotif($paramsB, $userID) {

        $user = Person::getSimpleUserById($userID);
        if (!empty($user["email"])) {
            $params = array (
                "type" => Cron::TYPE_MAIL,
                "tpl"=>'bookmarkNotif',
                "subject" => "[".self::getAppName()."] - Nouvelles annonces, ".@$user["name"],
                "from"=>Yii::app()->params['adminEmail'],
                "to" => $user["email"],
                "tplParams" => array(
                    "userName" => @$user["name"],
                    "logo"=> Yii::app()->params["logoUrl"],
                    "logo2" => Yii::app()->params["logoUrl2"],
                    "params" => $paramsB,
                    "baseUrl" => Yii::app()->getRequest()->getBaseUrl(true)."/"
                ),
            );
            
            Mail::schedule($params);
        }
        // }
    }

    public static function initTplParams($params) {
        $tplP = array(  "logo"=> Yii::app()->params["logoUrl"],
                        "logo2" => Yii::app()->params["logoUrl2"],
                        "baseUrl" => Yii::app()->getRequest()->getBaseUrl(true)."/");

        $res = array_merge($tplP, $params);
        //Rest::json($res); exit;
        return $res;
    }

    public static function createAndSend($params) {

        if(!empty($params["tplMail"])) {
            $res = array (
                "type" => Cron::TYPE_MAIL,
                "tpl"=>$params["tpl"],
                "subject" => $params["tplObject"],
                "from"=>Yii::app()->params['adminEmail'],
                "to" => $params["tplMail"],
                "tplParams" => self::initTplParams($params),
            );
            $res=self::getCustomMail($res);

            //Rest::json($res); exit;
            Mail::schedule($res);
        }else{
            throw new CTKException(Yii::t("common","Missing email!"));
        }
    }
}