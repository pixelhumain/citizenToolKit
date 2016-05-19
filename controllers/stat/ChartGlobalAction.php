<?php
/**
* to create statistic
* Can be launch by cron
*/
class ChartGlobalAction extends CAction
{
    public function run() {

		$controller = $this->getController();


		$params = array();

		//We have to send data names to group to the charts
		$params['groups'] = Lists::get(array('organisationTypes', 'eventTypes', 'actionRoomsTypes'));
		$page =  "chartGlobal";

		if(Yii::app()->request->isAjaxRequest){
			echo $controller->renderPartial($page,$params,true);
		}
		else {
			$controller->render($page,$params);
		}

    }
}
