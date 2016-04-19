<?php
/**
 * [actionGetWatcher get the user data based on his id]
 * @param  [string] $email   email connected to the citizen account
 * @return [type] [description]
 */
class AddActionAction extends CAction
{
    public function run()
    {
        //User must be login to do action
        $userId = Yii::app()->session["userId"];
        if ( Person::logguedAndValid() ) { 
        		        if (@$_POST["reason"])
	        	$reason=$_POST["reason"];
	        else
	        	$reason="";
	        $res = Action::addAction($userId , $_POST['id'], $_POST['collection'],$_POST['action'], isset($_POST['unset']), isset($_POST["multiple"]), $reason );        
        } else {
        	$res = array("result" => false, "msg" => "Please Log in order to vote ! ");
        }
        Rest::json( $res );
        Yii::app()->end();
    }
}