<?php
/**
* Update an information field for a person
*/
class UpdateWithJsonAction extends CAction
{
    public function run()
    {
        $controller=$this->getController();
        $res = Person::updateWithJson($_POST["file"][0]);
        //$res = array();
        return Rest::json($res);
    }
}

?>