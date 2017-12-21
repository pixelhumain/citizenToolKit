<?php
/**
 */
class GetListCitiesAction extends CAction
{
	public function run($insee, $zone)
    {
        $fields = array("insee", "name");
        if($zone == "departement")
            $cities = City::getDepartementCitiesByInsee($insee);
        else if($zone == "region")
            $cities = City::getRegionCitiesByInsee($insee);

        if(isset($cities))
        {
            $lescities = [];
            foreach ($cities as $keyCities => $city) {
                //foreach ($city as $keyCity => $valueCity) {
                    //var_dump($valueCity);
                    $infoCity["insee"] = $city["insee"];
                    $infoCity["name"] = $city["name"];
                    $lescities[] = $infoCity;
                
            }
            Rest::json(array('result' => true,
                                'cities' => $lescities)); 
        }    
        else
            Rest::json(array('result' => false));
    }
}
?>