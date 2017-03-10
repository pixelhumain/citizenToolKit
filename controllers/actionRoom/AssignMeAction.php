<?php
class AssignMeAction extends CAction
{
    public function run()
    {
        $res = Actions::assignMe( $_POST );
        Rest::json( $res );
        Yii::app()->end();
    }
    
}