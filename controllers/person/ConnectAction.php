<?php
/**
 * connect 2 people together 
 */
class ConnectAction extends CAction
{
    public function run($id,$type)
    {
        Rest::json( Link::connect(Yii::app()->session['userId'], PHType::TYPE_CITOYEN, $id, $type,Yii::app()->session['userId'], "knows" ));
    }
}