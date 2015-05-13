<?php
class SaveAction extends CAction
{
    public function run()
    {
        $controller=$this->getController();
        if( isset($_POST['title']) && !empty($_POST['title']))
        {
            //TODO check by key
            if(!Event::checkExistingEvents($_POST))
            { 
              $res = Event::saveEvent($_POST);
              Rest::json($res);
            } else
              Rest::json(array("result"=>false, "msg"=>"Cette Evenement existe déjà."));
        } else
            Rest::json(array("result"=>false, "msg"=>"Cette requete ne peut aboutir."));
        exit;
    }
}