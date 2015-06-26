<?php
/**
 * connect 2 people together 
 */
class ConnectAction extends CAction
{
    public function run($id,$type, $ownerLink, $targetLink = null)
    {
        Rest::json( Link::connectPerson(Yii::app()->session['userId'], Person::COLLECTION, $id, $type,  $ownerLink, $targetLink ));
    }
}