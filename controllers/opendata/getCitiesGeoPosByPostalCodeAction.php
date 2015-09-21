<?php
/**
 * Retrieve all the Countries 
 * @return [json] {value : "codeinsee", text : "the Text"}
 */
class GetCitiesGeoPosByPostalCodeAction extends CAction
{
    public function run()
    {
        $errorMessage = array(array("value" => "", "text" => Yii::t("openData","Unknown Postal Code")));
        $cities = array();
        $postalCode = isset($_POST["postalCode"]) ? $_POST["postalCode"] : null;
        try {
            $cities = SIG::getPositionByCp($postalCode);
        } catch (CTKException $e) {
            $cities = array("unknownId" => array("name" => Yii::t("openData","Unknown Postal Code"), "insee" => ""));
        }

        Rest::json($cities); 
        Yii::app()->end();
    }
}