<?php 
class GetThumbPathAction extends CAction {
    public function run() {
        $controller=$this->getController();
        $me = Person::getById(Yii::app()->session['userId']);
		Rest::json(array("profilImageUrl" => $me['profilImageUrl'] )); 
    	Yii::app()->end();
    }
}
?>