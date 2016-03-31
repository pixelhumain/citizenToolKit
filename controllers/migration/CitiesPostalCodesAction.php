<?php
class CitiesPostalCodesAction extends CAction
{
    public function run() {
        $controller=$this->getController();
        
        //For each citie on collection cities
        $nbCities = 0;
        $nbCitiesTotal = PHDB::count("cities");
        $lastname = "";
        $lastpourcent = 0;
        $sort = array();
        echo "Début du traitement de ".$nbCitiesTotal." cities </br>";
        while ($nbCities < $nbCitiesTotal) {
            if ($lastname != "") $sort = array('_id' => array('$gt' => new MongoId($lastname)));
            $cities = PHDB::findAndSort("cities", $sort, array('_id' => 1), 2000);
            $nbDoublon=0;;
            foreach ($cities as $cityId => $aCity) {
                $nbCities++;
                $pourcent = $nbCities / $nbCitiesTotal * 100;
                if ($pourcent >= $lastpourcent) {
                    $lastpourcent++;
                    echo "Pourcentage : ".$pourcent.'</br>';
                }
                //Look in newCities Collection if the entry already exists
                $insee = $aCity["insee"];
                $newCity = PHDB::findOne("newCities", array("insee" => $insee));
                if ($newCity) {
                    echo "Gestion du double : ".$insee.'. Regroupement de '.$newCity["alternateName"].' avec '.$aCity["alternateName"].'</br>';
                    $nbDoublon++;
                    //create new postal code
                    $postalCodes = array(
                                    "postalCode" => $aCity["cp"],
                                    "name"=>$aCity["alternateName"],
                                    "geo"=>$aCity["geo"],
                                    "geoPosition"=>$aCity["geoPosition"]
                    );
                    //Add an entry in postalCodes array
                    $query = array('$addToSet' => array("postalCodes" => $postalCodes));
                    PHDB::update("newCities", array("_id" => $newCity["_id"]),$query);
                } else {
                    //Create an entry in newCity collection
                    $newCity = $aCity;
                    unset($newCity["cp"]);
                    //create new postal code
                    $postalCodes = array(array("postalCode" => $aCity["cp"],
                                    "name"=>$aCity["alternateName"],
                                    "geo"=>$aCity["geo"],
                                    "geoPosition"=>$aCity["geoPosition"]
                    ));
                    $newCity["postalCodes"] = $postalCodes;
                    PHDB::insert("newCities", $newCity);
                }
                $lastname = (String) $aCity["_id"];
            }
            echo "Nb cities transformées : ".$nbCities.'</br>';
            echo "Last AlternateName :".$lastname."</br>";
        }
        echo $nbCities." ont été traitées et transformées avec le nouveau format</br>";
        echo $nbDoublon." doublons de code insee ont été regroupé sous le même code insee</br>";
    }
}