<?php
class CloseActionAction extends CAction
{
    public function run()
    {
        $res = ActionRoom::closeAction( $_POST );
        Rest::json( $res );
        Yii::app()->end();
    }
    
}