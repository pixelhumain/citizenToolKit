
<?php
/**
* retreive dynamically 
*/
class GetContactsByMailsAction extends CAction
{
    public function run() {
    	$res = (!empty($_POST["mailsList"] ) ? Element::getContactsByMails($_POST["mailsList"]) : array() );
		Rest::json($res);
		exit;
    }
}