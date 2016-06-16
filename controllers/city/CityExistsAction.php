<?php

class CityExistsAction extends CAction
{
    public function run( )
    {
        $insee      = isset($_POST["insee"])        ? $_POST["insee"] : null;
        $postalCode = isset($_POST["postalCode"])   ? $_POST["postalCode"] : null;
        $country    = isset($_POST["country"])      ? $_POST["country"] : null;
        $name       = isset($_POST["cityName"])     ? $_POST["cityName"] : null;

        $res = City::getInternationalCity($postalCode, $insee, $country);

        if($res != null)
            Rest::json( array("res"=>true, "obj" => $res ));
        else{
            //si on a pas de résultat le premier coup, on fait un essai sans le code insee
            error_log("deuxieme essai");
            $res = City::getInternationalCity($postalCode, null, $country, $name);
            if($res != null)
                Rest::json( array("res"=>true, "obj" => $res ));
            else{
                //si toujours pas de résultat, on replace les "saint" par "st" et on supprime les tirets (-)
                $name = str_ireplace("saint", "st", $name);
                $name = str_ireplace("-", " ", $name);
                error_log("troisieme essai, name = ".$name);
                $res = City::getInternationalCity($postalCode, null, $country, $name);
                if($res != null)
                    Rest::json( array("res"=>true, "obj" => $res ));
                else
                    Rest::json( array("res"=>false, "obj" => null ));
            }
        }
    }
}
