<?php
class SourceAdminAction extends CAction
{
    public function run() {
        $controller = $this->getController();
        $params = array();

        $sourceAdmin = Person::getSourceAdmin(Yii::app()->session["userId"]);
        $result = array();

        if(!empty($sourceAdmin)){
            foreach ($sourceAdmin as $key => $value) {
                $result[$value] = Element::getAllEntitiesByKey($value);
            }
        }
        
        $params["entitiesSourceAdmin"] = $result;
        if(Yii::app()->request->isAjaxRequest)
            echo $controller->renderPartial("sourceadmin",$params,true);
        else 
            $controller->render("sourceadmin",$params);
    }
}

?>