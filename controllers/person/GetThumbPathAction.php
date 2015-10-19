<?php 
	$me = Person::getById(Yii::app()->session['userId']);
	Rest::json(array("profilImageUrl" => $me['profilImageUrl'] )); 
    Yii::app()->end();
?>