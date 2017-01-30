<?php
class CloseAction extends CAction
{
    public function run()
    {
        $res = Survey::closeEntry( $_POST );
        Rest::json( $res );
        Yii::app()->end();
    }
    
}