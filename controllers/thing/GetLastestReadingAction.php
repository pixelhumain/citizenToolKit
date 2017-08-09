<?php
/**
* @author :  Danzal
*/
class GetLastestReadingAction extends CAction {

    public function run($deviceId=null) { 

    	$controller=$this->getController();

        if(empty($deviceId)){
            //echo "Index thing\n";
            //var_dump(Thing::getAllValueSCKDevices());
            $params = array();
            //$params['device']=$device;
            if(Yii::app()->request->isAjaxRequest)
                echo $controller->renderPartial("scklastestreadings",$params,true);
            else 
                $controller->render("scklastestreadings",$params);
        }else{
            //partie pour l'actualisation des données sur la page dernière mesures
            if (preg_match("/(1-9[0-9]{9})/ ",$deviceId)){
				$device = Thing::getSCKDeviceMdata(
					Thing::COLLECTION,$where=
						array("type"=>Thing::SCK_TYPE,'deviceId'=>$deviceId));
					$boardId=$device['boardId'];
				if(!empty($boardId) && $boardId!='[FILTERED]'){
					$res['viaCODB']=Thing::getLastestRecordsInDB($boardId);
				//$res['boardId']=$boardId;
				}else{
					$res['viaCODB']=array('result'=>false,'boardId'=>$boardId);
				}
				Rest::json($res);
				Yii::app()->end();
            }
        }   
    }
}
?>