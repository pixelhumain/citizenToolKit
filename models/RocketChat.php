<?php 
class RocketChat{

	public static function getToken($email,$pwd) 
	{ 
		$res = array(
			"loginToken"=>null,
			"rocketUserId" => null,
			"msg" => ""
		);
		if( @$_SESSION["loginToken"] && @$_SESSION["rocketUserId"]){
			$res["loginToken"] = $_SESSION["loginToken"];
	  		$res["rocketUserId"] = $_SESSION["rocketUserId"];
	  		$res["msg"] = "user logged in from session";
		} else {
			define('REST_API_ROOT', '/api/v1/');
			define('ROCKET_CHAT_INSTANCE', Yii::app()->params['rocketchatURL']);

			try{
				Yii::import('rocketchat.RocketChatClient', true);
				Yii::import('rocketchat.RocketChatUser', true);
				Yii::import('rocketchat.RocketChatChannel', true);
				Yii::import('rocketchat.RocketChatGroup', true);

				Yii::import('httpful.Request', true);
				Yii::import('httpful.Bootstrap', true);
				$user = new \RocketChat\User($email, $pwd);

			
				$log = $user->login();
				//var_dump($log);
				if($log->status == "success"){
					$res["loginToken"] = $user->authToken;
					$res["rocketUserId"] = $user->id;
					$res["msg"] = "user logged in";
					unset(Yii::app()->session["pwd"]);
				} else {
					$res["msg"] = "mail or pwd don't exist or match,can't log into RC : ".$log->message;	
					$res["error"] = "unauthorised";
				}
			} catch (Exception $e) {
	            $res["msg"] = $e->getMessage();
	            $res["error"] = "noHost";
			}
		} 
		return $res;	
	}

	public static function createGroup($name,$type=null,$username,$inviteOnly=null) 
	{ 
		define('REST_API_ROOT', '/api/v1/');
		define('ROCKET_CHAT_INSTANCE', Yii::app()->params['rocketchatURL']);

		try{
			Yii::import('rocketchat.RocketChatClient', true);
			Yii::import('rocketchat.RocketChatUser', true);
			Yii::import('rocketchat.RocketChatChannel', true);
			Yii::import('rocketchat.RocketChatGroup', true);
			Yii::import('httpful.Request', true);
			Yii::import('httpful.Bootstrap', true);
			
			$channel = ( $type == "channel" ) ? new \RocketChat\Channel( $name,array(),true ) : new \RocketChat\Group( $name,array(),true );
			
			$res = (object)array(
				"name" => $name,
				"type" => ( $type == "channel" ) ? "channel":"group",
				"username" => $username,
				/*"adminLoginToken" => Yii::app()->params["adminLoginToken"],
		    	"adminRocketUserId" => Yii::app()->params["adminRocketUserId"]*/
			);
			if(!$inviteOnly){
				$res->create = $channel->create();
				if(!@$res->create->success)
					$res->info = $channel->info() ;
				

			}
				
			if(@$username){
				$res->invite = $channel->invite($username) ;
				if(@$res->invite->channel->usernames)
					$channel->members = $res->invite->channel->usernames;			
			}
			//$res->channel = $channel;

		} catch (Exception $e) {
            $res->msg = $e->getMessage();
		}
		return $res;
	}


	public static function listUserChannels() { 
		
		define('REST_API_ROOT', '/api/v1/');
		define('ROCKET_CHAT_INSTANCE', Yii::app()->params['rocketchatURL']);

		Yii::import('rocketchat.RocketChatClient', true);
		Yii::import('rocketchat.RocketChatUser', true);
		Yii::import('httpful.Request', true);
		Yii::import('httpful.Bootstrap', true);
		$api = new \RocketChat\Client();
		$logged = new \RocketChat\User(Yii::app()->session['userEmail']);
		
		return $logged->listJoined();
    }

}