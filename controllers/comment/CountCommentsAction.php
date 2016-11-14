<?php
class CountCommentsAction extends CAction
{
    public function run() {
        $controller=$this->getController();

        if ( isset(Yii::app()->session["userId"]) && @$_POST['from'] && @$_POST['type'] && @$_POST['id'] ) {
            $res = array( "count" => Comment::countFrom($_POST['from'],$_POST['type'],$_POST['id']), "time"=>time() ); 
        } else {
            $res = array("count" => -1 );
        }
        Rest::json( $res );
        Yii::app()->end();
    }
}

