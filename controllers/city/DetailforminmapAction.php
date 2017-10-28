<?php
class DetailforminmapAction extends CAction
{
    public function run() {
        $controller=$this->getController();
        $city = City::getDetailFormInMap($_POST["id"]);
        Rest::json($city);
        Yii::app()->end();
    }
}

?>