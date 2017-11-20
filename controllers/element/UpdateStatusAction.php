<?php
/**
   * Register a new user for the application
   * Data expected in the post : name, email, postalCode and pwd
   * @return Array as json with result => boolean and msg => String
   */
class UpdateStatusAction extends CAction
{
    public function run()
    {
      assert('!empty($_POST["type"]); //The type is mandatory');
      assert('!empty($_POST["id"]); //The id is mandatory');
      assert('!empty($_POST["action"]); //The action type is mandatory');

      $result = array("result"=>false, "msg"=>Yii::t("common", "Incorrect request"));
    
        $controller=$this->getController();
        if (! (Person::logguedAndValid() && Yii::app()->session["userIsAdmin"])) {
        Rest::json(array("result" => false, "msg" => "You are not super admin : you can not modify this role !"));
        return;
      }
      if($_POST["type"]==Person::COLLECTION){
        $res = Role::updatePersonRole($_POST["action"], $_POST["id"]);
        //$res=Preference::updateConfidentiality(Yii::app()->session["userId"],$_POST["typeEntity"],$_POST);
      }else{
        $res=Element::updateStatus($_POST["id"],$_POST["type"]);
      }
		  Rest::json($res);
    }
}