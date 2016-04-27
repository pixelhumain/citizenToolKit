<?php

class CTKAction extends CAction {
    
    protected $currentUserId;
    
    /**
     * Constructor.
     * @param CController $controller the controller who owns this action.
     * @param string $id id of the action.
     */
    public function __construct($controller,$id) {
        parent::__construct($controller,$id);
        $this->currentUserId = Yii::app()->session["userId"];
    }

    /**
     * Check if the user is loggued and his roles are ok
     * @return true if the current user is loggued and valid 
     */
    public function userLogguedAndValid() {
        if (isset(Yii::app()->session["userId"])) {
            $user = Person::getById(Yii::app()->session["userId"]);
            
            $valid = Role::canUserLogin($user, Yii::app()->session["isRegisterProcess"]);
            $isLogguedAndValid = (isset( Yii::app()->session["userId"]) && $valid["result"]);
        } else {
            $isLogguedAndValid = false;
        }

        return $isLogguedAndValid;
    }
}