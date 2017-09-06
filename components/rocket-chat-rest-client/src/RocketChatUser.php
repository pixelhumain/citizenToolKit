<?php

namespace RocketChat;

use Httpful\Request;
use RocketChat\Client;

class User extends Client {
	public $username;
	private $password;
	public $id;
	public $nickname;
	public $email;

	public function __construct($username, $password=null, $fields = array(),$admin=null){
		parent::__construct($admin);
		$this->username = $username;
		$this->password = $password;
		if( isset($fields['nickname']) ) {
			$this->nickname = $fields['nickname'];
		}
		if( isset($fields['email']) ) {
			$this->email = $fields['email'];
		}
	}

	/**
	* Authenticate with the REST API.
	*/
	public function login($save_auth = true) {
		$response = Request::post( $this->api . 'login' )
			->body( array( 'user' => $this->username, 'password' => $this->password ) )
			->send();

		if( $response->code == 200 && isset($response->body->status) && $response->body->status == 'success' ) {
			if( $save_auth) {
				// save auth token for future requests
				$tmp = Request::init()
					->addHeader('X-Auth-Token', $response->body->data->authToken)
					->addHeader('X-User-Id', $response->body->data->userId);
				Request::ini( $tmp );
			}
			$this->id = $response->body->data->userId;
			$this->data = $response->body->data;
			$this->authToken = $response->body->data->authToken;
			$this->status = $response->body->status;
		} /*else {
			echo( "<b>".$response->body->message . "</b><br/>" );
		}*/
		return $response->body;
	}

	/**
	* Gets a user’s information, limited to the caller’s permissions.
	*/
	public function info() {
		$response = Request::get( $this->api . 'users.info?username=' . $this->username )->send();

		if( $response->code == 200 && isset($response->body->success) && $response->body->success == true ) {
			$this->id = $response->body->user->_id;
			$this->nickname = $response->body->user->name;
			$this->email = $response->body->user->emails[0]->address;
		} 
		return $response->body;
	}

	/**
	* Create a new user.
	*/
	public function create() {
		$response = Request::post( $this->api . 'users.create' )
			->body(array(
				'name' => $this->nickname,
				'email' => $this->email,
				'username' => $this->username,
				'password' => $this->password,
			))
			->send();

		if( $response->code == 200 && isset($response->body->success) && $response->body->success == true ) {
			$this->id = $response->body->user->_id;
			return $response->body->user;
		} else {
			echo( "<b>".$response->body->error . "</b><br/>" );
			return false;
		}
	}

	/**
	* Deletes an existing user.
	*/
	public function delete() {

		// get user ID if needed
		if( !isset($this->id) ){
			$this->me();
		}
		$response = Request::post( $this->api . 'users.delete' )
			->body(array('userId' => $this->id))
			->send();

		if( $response->code == 200 && isset($response->body->success) && $response->body->success == true ) {
			return true;
		} else {
			echo( "<b>".$response->body->error . "</b><br/>" );
			return false;
		}
	}

	/**
	* Post a message in this channel, as the logged-in user
	*/
	public function postMessage( $text ) {
		$message = is_string($text) ? array( 'text' => $text ) : $text;
		if( !isset($message['attachments']) ){
			$message['attachments'] = array();
		}

		$response = Request::post( $this->api . 'chat.postMessage' )
			->body( array_merge(array('channel' => '@'.$this->username ), $message) )
			->send();

		if( $response->code == 200 && isset($response->body->success) && $response->body->success == true ) {
			return true;
		} else {
			if( isset($response->body->error) )	echo( "<b>".$response->body->error . "</b><br/>" );
			else if( isset($response->body->message) )	echo( "<b>".$response->body->message . "</b><br/>" );
			return false;
		}
	}

	/*
	https://rocket.chat/docs/developer-guides/rest-api/channels/list-joined
	Lists all of the channels the calling user has joined, this method supports the Offset and Count Query Parameters.
	*/
	public function listJoined() {
		$tmp = Request::init()
			->addHeader('X-Auth-Token', $_SESSION["loginToken"])
			->addHeader('X-User-Id', $_SESSION["rocketUserId"]);
		Request::ini( $tmp );

		$response = Request::get( $this->api . 'channels.list.joined' )->send();
		$list = array();
		if( $response->code == 200 && isset($response->body->success) && $response->body->success == true ) {
			foreach ($response->body->channels as $key => $value) {
				$list[] = $value->name;
			}
		}
		
		$response = Request::get( $this->api . 'groups.list' )->send();
		if( $response->code == 200 && isset($response->body->success) && $response->body->success == true ) {
			foreach ($response->body->groups as $key => $value) {
				$list[] = $value->name;
			}
		} 
		
		return $list;
	}


}
