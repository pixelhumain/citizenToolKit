<?php
/**
* retreive dynamically 
*/
class SendMailFormContactAction extends CAction
{
    public function run() {
    	$controller=$this->getController();
        
        $email = isset($_POST['email']) ? $_POST['email'] : "";
        Mail::sendMailFormContact($_POST['email'], $_POST['name'], $_POST['subject'], $_POST['message']);

    	Rest::json(array("res"=>$res));
        Yii::app()->end();
    }
}