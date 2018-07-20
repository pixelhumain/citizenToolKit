<?php
class GetGeoshapeAction extends CAction
{
    public function run() {
        $controller=$this->getController();
        $cities = City::getGeoShapeCity($_POST);
        Rest::json($cities);
        Yii::app()->end();
    }
}

?>