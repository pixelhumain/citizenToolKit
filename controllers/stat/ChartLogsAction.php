<?php
/**
* to create statistic
* Can be launch by cron
*/
class ChartLogsAction extends CAction
{
    public function run() {

		$controller = $this->getController();


		$params = array();		
		$page =  "chartLogs";

		$params['actionsLog'] = Log::getActionsToLog();
		$params['groups']['resultTypes'] = array('0'=> 'false', '1' => 'true');
		if(Yii::app()->request->isAjaxRequest){
			echo $controller->renderPartial($page,$params,true);
		}
		else {
			$controller->render($page,$params);
		}

    }
}
