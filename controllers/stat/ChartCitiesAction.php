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
		$res = PHDB::find('cities', array('insee' => array('$exists' => 1)), array("insee"=>1, 'name' => 1, 'dep' => 1,'depName' => 1,'region' => 1,'regionName' => 1,));
		foreach($res as $key => $city){
			if(isset($city['insee']))$params['cities'][$city['insee']] = @$city['name'];
			if(isset($city['dep']))$params['dep'][$city['dep']] = @$city['depName'];
			if(isset($city['region']))$params['region'][$city['region']] = @$city['regionName'];
		}


  		// $params["nbCitiesDepartement"] = count(City::getDepartementCitiesByInsee($insee));
  		// $params["nbCitiesRegion"] = count(City::getRegionCitiesByInsee($insee));
     	// $params["cityData"] = $cityDataTest;
      //   $params["title"] = "Population/An";

      //   $params["name_id"] = $typeData;
      //   if(isset($type))
      //       $params["name_id"] = $params["name_id"] . $type;
      //   if(isset($typeGraph))
      //       $params["name_id"] = $params["name_id"] . $typeGraph;
      //   if(isset($option))
      //   {
      //       $params["optionData"] = $option;
      //       foreach ($option as $key => $value){
      //           $exploseOption = explode('.', $value);
      //           foreach ($exploseOption as $k => $v){
      //               $params["name_id"] = $params["name_id"] .  $v;
      //           }
      //       }
           
      //   } 

      //   if($inseeCities!=null)  
      //   {
      //       $params["inseeCities"] = $cities;
      //       foreach ($cities as $key => $value){
      //           $params["name_id"] = $params["name_id"] .  $value;  
      //       }
           
        // } 


		// $params["nbCitiesDepartement"] = count(City::getDepartementCitiesByInsee(59350));
  //       $params["nbCitiesRegion"] = count(City::getRegionCitiesByInsee(59350));


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
