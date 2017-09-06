<?php

namespace RocketChat;

use Httpful\Request;
use Yii;

class Client{

	public $api;

	function __construct($admin=null){
		$this->api = ROCKET_CHAT_INSTANCE . REST_API_ROOT;

		// set template request to send and expect JSON
		// user is loggued from CO , when entering CO
		if($admin!=null)
			$tmp = Request::init()
				->sendsJson()
				->expectsJson()
	            ->addHeader( 'X-Auth-Token', @$_SESSION["adminLoginToken"] )
	            ->addHeader( 'X-User-Id', @$_SESSION["adminRocketUserId"] );
	    else if($admin=="noHeader")
			$tmp = Request::init()
				->sendsJson()
				->expectsJson();
		else
			$tmp = Request::init()
				->sendsJson()
				->expectsJson()
	            ->addHeader( 'X-Auth-Token', @$_SESSION["loginToken"] )
	            ->addHeader( 'X-User-Id', @$_SESSION["rocketUserId"] );
        
		Request::ini( $tmp );
	}

	/**
	* Get version information. This simple method requires no authentication.
	*/
	public function version() {
		$response = \Httpful\Request::get( $this->api . 'info' )->send();
		return $response->body->info->version;
	}

	/**
	* Quick information about the authenticated user.
	*/
	public function me() {
		$response = Request::get( $this->api . 'me' )->send();

		if( $response->body->status != 'error' ) {
			if( isset($response->body->success) && $response->body->success == true ) {
				return $response->body;
			}
		} else {
			echo( "<b>".$response->body->message . "</b><br/>" );
			return false;
		}
	}

	/**
	* List all of the users and their information.
	*
	* Gets all of the users in the system and their information, the result is
	* only limited to what the callee has access to view.
	*/
	public function list_users(){
		$response = Request::get( $this->api . 'users.list' )->send();

		if( $response->code == 200 && isset($response->body->success) && $response->body->success == true ) {
			return $response->body->users;
		} else {
			echo( "<b>".$response->body->error . "</b><br/>" );
			return false;
		}
	}

	/**
	* List the private groups the caller is part of.
	*/
	public function list_groups() {
		$response = Request::get( $this->api . 'groups.list' )->send();

		if( $response->code == 200 && isset($response->body->success) && $response->body->success == true ) {
			$groups = array();
			foreach($response->body->groups as $group){
				$groups[] = new Group($group);
			}
			return $groups;
		} else {
			echo( "<b>".$response->body->error . "</b><br/>" );
			return false;
		}
	}

	/**
	* List the channels the caller has access to.
	*/
	public function list_channels() {
		$response = Request::get( $this->api . 'channels.list' )->send();

		if( $response->code == 200 && isset($response->body->success) && $response->body->success == true ) {
			$groups = array();
			foreach($response->body->channels as $group){
				$groups[] = new Channel($group);
			}
			return $groups;
		} else {
			echo( "<b>".$response->body->error . "</b><br/>" );
			return false;
		}
	}

}
