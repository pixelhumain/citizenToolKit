<?php
class CloseActionAction extends CAction
{
    public function run()
    {
        $res = Actions::closeAction( $_POST );
        Rest::json( $res );
        Yii::app()->end();
    }
    
}