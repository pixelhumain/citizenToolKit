<?php
  
  /**
   * Register to a secure application, the unique pwd is linked to the application instance retreived by type
   * the appKey is saved in a sessionvariable loggedIn
   * for the moment works with a unique password for all users 
   * specified on the event instance 
   * Steps : 
   * 1- find the App (ex:event in group) exists in appType table
   * 2- check if email is valid
   * 3- test password matches
   * 4- find the user exists in "citoyens" table based on email
   * 5- save session information 
   */
class LoginAction extends CAction
{
    public function run()
    {
        $controller=$this->getController();
        $controller->layout = "//layouts/mainSimple";
        if(Yii::app()->request->isAjaxRequest && isset($_POST['registerEmail']) && !empty($_POST['registerEmail']) 
                                            && isset($_POST['registerPwd']) && !empty($_POST['registerPwd']))
        {
            //check application exists
            if(isset($_POST['appKey']) && !empty($_POST['appKey']) 
                 && isset($_POST['appType']) && !empty($_POST['appType']))
              {
                 $type = Yii::app()->mongodb->selectCollection($_POST['appType']);
                 $app = $type->findOne(array("_id"=>new MongoId($_POST['appKey'])));
                   if($app)
                   {
                        //validate isEmail
                        $email = $_POST['registerEmail'];
                        $name = "";
                       if(preg_match('#^([\w.-])/<([\w.-]+@[\w.-]+\.[a-zA-Z]{2,6})/>$#',$email, $matches)) 
                       {
                          $name = $matches[0];
                          $email = $matches[1];
                       }
                       if(preg_match('#^[\w.-]+@[\w.-]+\.[a-zA-Z]{2,6}$#',$email)) 
                       { 
                            //test pwd
                            if( $app["pwd"] == $_POST['registerPwd'] )
                            {
                                $account = PHDB::findOne(Person::COLLECTION,array("email"=>$_POST['registerEmail']));
                                if($account){
                                    //TODO : check if account is participant in the app
                                    Yii::app()->session["userId"] = $account["_id"];
                                    Yii::app()->session["userEmail"] = $account["email"];
                                    if ( !isset(Yii::app()->session["loggedIn"]) && !is_array(Yii::app()->session["loggedIn"]))
                                        Yii::app()->session["loggedIn"] =   array();
                                    $tmp = Yii::app()->session["loggedIn"];
                                    array_push( $tmp , $_POST['appKey'] );
                                    
                                    Yii::app()->session["loggedIn"] = $tmp;
                                    echo json_encode(array("result"=>true, "msg"=>"Vous êtes connecté à présent, Amusez vous bien."));
                                }
                                else
                                     echo json_encode(array("result"=>false, "msg"=>"Compte inconnue."));
                            }else
                                echo json_encode(array("result"=>false, "msg"=>"Accés refusé."));
                        } else 
                           echo json_encode(array("result"=>false, "msg"=>"Email invalide"));
                   } else 
                       echo json_encode(array("result"=>false, "msg"=>"Application invalide"));
            }else{
                    echo json_encode(array("result"=>false, "msg"=>"Vous Pourrez pas accéder a cette application"));
                }
        } else
            echo json_encode(array("result"=>false, "msg"=>"Cette requete ne peut aboutir."));
        
        exit;
    }
}

