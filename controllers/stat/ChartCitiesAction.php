<?php
/**
* to create statistic
* Can be launch by cron
*/
class ChartCitiesAction extends CAction
{
    public function run() {

		$controller = $this->getController();


		$params = array();

		//We have to send data names to group to the charts
		$params['groups'] = Lists::get(array('organisationTypes', 'eventTypes'));
		$page =  "chartCities";

		if(Yii::app()->request->isAjaxRequest){
			echo $controller->renderPartial($page,$params,true);
		}
		else {
			$controller->render($page,$params);
		}

    }
}
