<?php
/**
 * disconnect 2 people together 
 */
class DisconnectAction extends CAction
{
    public function run($id,$type, $ownerLink, $targetLink = null)
    {
        Rest::json( Link::disconnectPerson(Yii::app()->session['userId'], Person::COLLECTION, $id, $type, $ownerLink, $targetLink));
    }
}