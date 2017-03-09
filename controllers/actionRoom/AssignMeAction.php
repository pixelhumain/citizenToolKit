<?php
class AssignMeAction extends CAction
{
    public function run()
    {
        $res = ActionRoom::assignMe( $_POST );
        Rest::json( $res );
        Yii::app()->end();
    }
    
}