<?php
/**
 * Send an mail to a user with the email.
 * Depending of the type. Could be
 * - password = to send a new password to the user
 * - validation = to send the validation email to the user
 * @return [json] 
 */
class SendEmailAction extends CAction {
    public function run() {
        $email = $_POST["email"];
        $type = $_POST["type"];
        
        //Check user existance
        $user = PHDB::findOne(PHType::TYPE_CITOYEN,array( "email" => $email));
        if (!$user) {
            Rest::json(array("result"=>false, "errId" => "UNKNOWN_ACCOUNT_ID", 
                         "msg"=>"Cet email n'existe pas dans notre base. Voulez vous créer un compte ?"));
            die();
        }

        // Forgot my password Mail
        if ($type == "password") {
            //reset password 
            $pwd = self::random_password(8);
            //TODO SBAR : Call the model
            PHDB::update( PHType::TYPE_CITOYEN,
                            array("email" => $email), 
                            array('$set' => array("pwd"=>hash('sha256', $email.$pwd))));

            //TODO SBAR : Application - how does it work exactly ?
            //Same user for different application ? Application = Granddir ? Larges ? Communecter ?
            if (empty($_POST["app"])) {
                $app = new Application("");
            } else {
                $app = new Application($_POST["app"]);
            }
            
            //send validation mail
            Mail::passwordRetreive($email, $pwd);
            $res = array("result"=>true, "msg"=>"Un mail avec un nouveau mot de passe vous a été envoyé à votre adresse email. Merci.");
        
        // Validation Mail
        } else if ($type == "validateEmail") {
            Mail::validatePerson($user);
            $res = array("result"=>true,"msg"=>"Un mail de validation vous a été envoyé à votre adresse email.");
        } else {
            $res = array("result"=>true,"msg"=>"Unknow email type : please contact your admin !");
        }

        Rest::json($res);  
        Yii::app()->end();
    }

    //TODO SBAR : Move to the person model
    public function random_password( $length = 8 ) {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_-=+;:,.?";
        $password = substr( str_shuffle( $chars ), 0, $length );
        return $password;
    }
}