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
        if(@Yii::app()->session["userId"]){
          if(@$_POST["settings"]){
            if($_POST["type"]==Person::COLLECTION && $_POST["id"]==Yii::app()->session["userId"])
              $res=Preference::updatePreferences(Yii::app()->session["userId"], $_POST["type"], @$_POST["settings"], @$_POST["value"], @$_POST["subName"]);
            else
              $res=Preference::updateSettings(Yii::app()->session["userId"],$_POST);
          }
          else if(@$_POST["type"])
            $res=Preference::updateConfidentiality(Yii::app()->session["userId"],$_POST["typeEntity"],$_POST);
          else
            $res=Preference::updatePreferences(Yii::app()->session["userId"],$_POST["typeEntity"], @$_POST["name"], @$_POST["value"]);
		    }else
          $res=array("result"=>false, "msg"=>Yii::t("common", "You are not connected"));
      Rest::json($res);
		  exit;
    }
}