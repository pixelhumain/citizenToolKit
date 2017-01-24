<?php
/**
   * Register a new user for the application
   * Data expected in the post : name, email, postalCode and pwd
   * @return Array as json with result => boolean and msg => String
   */
class UpdateSettingsAction extends CAction
{
    public function run()
    {
        $controller=$this->getController();
        if(isset($_POST["type"])){
          $res=Preference::updateConfidentiality(Yii::app()->session["userId"],$_POST["typeEntity"],$_POST);
        }else{
          $res=Preference::updatePreferences(Yii::app()->session["userId"],$_POST["typeEntity"]);
        }
		Rest::json($res);
		exit;
    }
}