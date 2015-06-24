<?php
/**
 * disconnect 2 people together 
 */
class DisconnectAction extends CAction
{
    public function run($id,$type)
    {
        Rest::json( Link::disconnectPerson(Yii::app()->session['userId'], PHType::TYPE_CITOYEN, $id, $type,"knows", "knows" ));
    }
}