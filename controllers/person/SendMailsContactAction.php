<?php
/**
 */
class SendMailsContactAction extends CAction
{
	public function run()
    {
        if(!empty($_POST['arrayMails']))
        {
            $ad = array();
            foreach ($_POST['arrayMails'] as $keyMail => $mail){

                //if(DateValidator::toValidate($mail) == "")
                //{
                    $name = explode("@", $mail);
                    $member = array(
                             'name'=>$name[0],
                             'email'=>$mail,
                             'created' => time(),
                             'type'=>'citoyen',
                             "links" => array( 
                                'knows'=>array( Yii::app()->session["userId"] => array( "type" => "citoyen") ),
                                'invitedBy'=>array(Yii::app()->session["userId"] => array( "type" => "citoyen")),
                                ),
                             );
                    $ad[] = $member ;
                    Person::createAndInvite($member); 
                //}
            }
            Rest::json(array('result' => true, "ad" => $ad));
        }
        else
        {
            Rest::json(array('result' => false));
        }
    }
}

?>