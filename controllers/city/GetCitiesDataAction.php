<?php
/**
 */
class GetCitiesDataAction extends CAction
{
	 public function run($insee, $typeData, $type=null)
    {
        if(isset($_POST['cities']))
        {
            $listInsee[] = $insee;
            foreach ($_POST['cities'] as $key => $value) {
               $listInsee[] = $value;
            }

            $citiesData = City::getDataByListInsee($listInsee, $typeData);
        
            Rest::json($citiesData);
        }
        else
        {
            Rest::json(array('result' => false));
        }
    }
}