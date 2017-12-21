<?php
class IndexAction extends CAction
{
    public function run($type=null, $id=null)
    {
        $controller=$this->getController();

        //TODO type and id 
        //are used to calculate the gamification points of any entity

        $params = array();
        $controller->render( "index" , $params );
    }

 
}